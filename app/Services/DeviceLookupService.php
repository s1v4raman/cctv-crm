<?php

namespace App\Services;

use App\Models\InstalledEquipment;
use App\Models\Product;
use App\Models\ProductAlias;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;

class DeviceLookupService
{
    /**
     * Assemble and resolve a multi-code scan session or single barcode lookup.
     *
     * @param  array<int, array{raw?: string, format?: string|null}>  $scans
     */
    public function lookup(array $scans, User $user): array
    {
        $parsed = $this->parseScans($scans);

        $ean = $parsed['ean'];
        $mac = $parsed['mac'];
        $possibleMac = $parsed['possible_mac'];
        $serialCandidates = $parsed['serial_candidates'];
        $modelTokens = $parsed['model_tokens'];
        $unknownTokens = $parsed['unknown_tokens'];

        $primarySerial = $serialCandidates[0] ?? '';

        // 1. Check InstalledEquipment ONLY when serial or strict MAC is non-empty
        $equipment = null;
        if (count($serialCandidates) > 0 || $mac !== '') {
            $query = InstalledEquipment::with(['lead', 'product', 'installationJob']);
            $query->where(function ($q) use ($serialCandidates, $mac) {
                foreach ($serialCandidates as $idx => $sn) {
                    if ($idx === 0) {
                        $q->whereRaw('UPPER(TRIM(serial_number)) = ?', [$sn]);
                    } else {
                        $q->orWhereRaw('UPPER(TRIM(serial_number)) = ?', [$sn]);
                    }
                }
                if ($mac !== '') {
                    if (count($serialCandidates) > 0) {
                        $q->orWhereRaw('UPPER(TRIM(mac_address)) = ?', [$mac]);
                    } else {
                        $q->whereRaw('UPPER(TRIM(mac_address)) = ?', [$mac]);
                    }
                }
            });
            $equipment = $query->first();
        }

        if ($equipment) {
            return $this->formatInstalledEquipmentResponse(
                $equipment,
                $user,
                $ean,
                $mac,
                $possibleMac,
                $serialCandidates,
                $unknownTokens
            );
        }

        // 2. Match Product via products table & approved product_aliases (no prefix guessing)
        $product = $this->findExactProduct($ean, $modelTokens, $serialCandidates);

        if ($product) {
            // Strip any product model / sku / name from serial candidates so model is NEVER treated as serial
            $modelStrings = array_map('strtoupper', array_map('trim', array_filter([
                $product->model_no,
                $product->sku,
                $product->name,
            ])));

            $cleanSerialCandidates = array_values(array_filter($serialCandidates, function ($sn) use ($modelStrings) {
                return !in_array(strtoupper(trim($sn)), $modelStrings, true);
            }));

            $resolvedSerial = $cleanSerialCandidates[0] ?? '';

            $displayName = $product->name . ($product->model_no ? " ({$product->model_no})" : '');
            $productData = [
                'id'              => $product->id,
                'name'            => $product->name,
                'brand'           => $product->brand,
                'model_no'        => $product->model_no,
                'sku'             => $product->sku,
                'ean'             => $product->ean,
                'category'        => $product->category,
                'warranty_months' => $product->default_warranty_months,
            ];

            if ($user->isOffice()) {
                $productData['stock_quantity'] = $product->stock_quantity;
                $productData['unit_price'] = $product->unit_price;
            }

            $payload = [
                'found'             => true,
                'type'              => 'catalog_product',
                'id'                => $product->id,
                'product_id'        => $product->id,
                'product_name'      => $product->name,
                'brand'             => $product->brand,
                'equipment_name'    => $displayName,
                'sku'               => $product->sku,
                'model_no'          => $product->model_no,
                'model'             => $product->model_no ?: $product->name,
                'serial'            => $resolvedSerial,
                'serial_number'     => $resolvedSerial,
                'serial_candidates' => $cleanSerialCandidates,
                'mac'               => $mac,
                'mac_address'       => $mac,
                'possible_mac'      => $possibleMac,
                'ean'               => $ean !== '' ? $ean : ($product->ean ?? ''),
                'is_duplicate'      => false,
                'model_not_found'   => false,
                'category'          => $product->category,
                'warranty_months'   => $product->default_warranty_months,
                'product'           => $productData,
                'candidates'        => [],
                'unknown_tokens'    => $unknownTokens,
            ];

            if ($user->isOffice()) {
                $payload['stock_quantity'] = $product->stock_quantity;
                $payload['unit_price'] = $product->unit_price;
            }

            return $payload;
        }

        // 3. No exact match: return up to 3 LIKE candidates (never auto-selected)
        $candidates = $this->findCandidateProducts($modelTokens, $serialCandidates, $unknownTokens);

        return [
            'found'             => false,
            'is_duplicate'      => false,
            'model_not_found'   => true,
            'message'           => 'Model not found in catalog. Pick it once, saved.',
            'serial'            => $primarySerial,
            'serial_number'     => $primarySerial,
            'serial_candidates' => $serialCandidates,
            'mac'               => $mac,
            'mac_address'       => $mac,
            'possible_mac'      => $possibleMac,
            'ean'               => $ean,
            'model'             => '',
            'model_no'          => '',
            'equipment_name'    => '',
            'product_name'      => '',
            'product_id'        => null,
            'product'           => null,
            'candidates'        => $candidates,
            'unknown_tokens'    => $unknownTokens,
        ];
    }

    /**
     * Split and classify raw scan tokens into ean, mac, possible_mac, serial_candidates, model_tokens, unknown_tokens.
     *
     * @param  array<int, array{raw?: string, format?: string|null}>  $scans
     */
    public function parseScans(array $scans): array
    {
        $ean = '';
        $mac = '';
        $possibleMac = [];
        $serialCandidates = [];
        $modelTokens = [];
        $unknownTokens = [];

        foreach ($scans as $scan) {
            $rawInput = trim((string) ($scan['raw'] ?? ''));
            $format = strtolower(trim((string) ($scan['format'] ?? '')));
            if ($rawInput === '') {
                continue;
            }

            // Clean barcode (strip AIM symbology identifiers like ]C1, ]c1, GS1 AIs, and control characters)
            $raw = self::cleanBarcode($rawInput);
            if ($raw === '') {
                continue;
            }

            // Explicit EAN format or standalone valid EAN
            if (in_array($format, ['ean_13', 'ean_8', 'ean'], true)) {
                if ($this->isValidEan($raw)) {
                    if ($ean === '') {
                        $ean = $raw;
                    }
                    continue;
                }
            }

            // Split QR/DataMatrix/composite text on newlines, commas, semicolons, pipes, and spaces
            $chunks = preg_split('/[\r\n,;|]+|\s+/', $raw) ?: [$raw];
            $prevLabel = null;

            foreach ($chunks as $chunk) {
                $token = self::cleanBarcode(trim($chunk));
                if ($token === '') {
                    continue;
                }

                // Handle standalone label tokens like "S/N:" followed by value in next chunk
                if (preg_match('/^(S\/N|SN|SERIAL|MAC|EAN|UPC|BARCODE|MODEL|MDL|PROD|ITEM|PART)[:=]?$/i', $token, $lbl)) {
                    $prevLabel = strtoupper($lbl[1]);
                    continue;
                }

                // Handle inline prefixed tokens like "SN:ABC123" or "MAC:AA:BB:CC:DD:EE:FF"
                if (preg_match('/^(S\/N|SN|SERIAL|MAC|EAN|UPC|BARCODE|MODEL|MDL|PROD|ITEM|PART)[:=](.+)$/i', $token, $pref)) {
                    $label = strtoupper($pref[1]);
                    $val = trim($pref[2]);
                    $prevLabel = null;

                    if (in_array($label, ['MAC'], true)) {
                        if ($this->isStrictMac($val)) {
                            if ($mac === '') {
                                $mac = $this->normalizeMac($val);
                            }
                        } elseif ($this->isBare12Hex($val)) {
                            $formatted = $this->normalizeMac($val);
                            if (!in_array($formatted, $possibleMac, true)) {
                                $possibleMac[] = $formatted;
                            }
                        }
                        continue;
                    }

                    if (in_array($label, ['EAN', 'UPC', 'BARCODE'], true)) {
                        if ($this->isValidEan($val) && $ean === '') {
                            $ean = $val;
                        }
                        continue;
                    }

                    if (in_array($label, ['MODEL', 'MDL', 'PROD', 'ITEM', 'PART'], true)) {
                        if ($val !== '' && !in_array($val, $modelTokens, true)) {
                            $modelTokens[] = $val;
                            $unknownTokens[] = $val;
                        }
                        continue;
                    }

                    if (in_array($label, ['S/N', 'SN', 'SERIAL'], true)) {
                        $normSn = $this->normalizeSerial($val);
                        if ($normSn !== '' && !in_array($normSn, $serialCandidates, true)) {
                            $serialCandidates[] = $normSn;
                            $unknownTokens[] = $normSn;
                        }
                        continue;
                    }
                }

                // Apply previous standalone label if present
                if ($prevLabel !== null) {
                    $lbl = $prevLabel;
                    $prevLabel = null;

                    if ($lbl === 'MAC') {
                        if ($this->isStrictMac($token)) {
                            if ($mac === '') {
                                $mac = $this->normalizeMac($token);
                            }
                        } elseif ($this->isBare12Hex($token)) {
                            $formatted = $this->normalizeMac($token);
                            if (!in_array($formatted, $possibleMac, true)) {
                                $possibleMac[] = $formatted;
                            }
                        }
                        continue;
                    }
                    if (in_array($lbl, ['EAN', 'UPC', 'BARCODE'], true)) {
                        if ($this->isValidEan($token) && $ean === '') {
                            $ean = $token;
                        }
                        continue;
                    }
                    if (in_array($lbl, ['MODEL', 'MDL', 'PROD', 'ITEM', 'PART'], true)) {
                        if (!in_array($token, $modelTokens, true)) {
                            $modelTokens[] = $token;
                            $unknownTokens[] = $token;
                        }
                        continue;
                    }
                    if (in_array($lbl, ['S/N', 'SN', 'SERIAL'], true)) {
                        $normSn = $this->normalizeSerial($token);
                        if ($normSn !== '' && !in_array($normSn, $serialCandidates, true)) {
                            $serialCandidates[] = $normSn;
                            $unknownTokens[] = $normSn;
                        }
                        continue;
                    }
                }

                // Unlabeled token classification:
                if ($this->isStrictMac($token)) {
                    if ($mac === '') {
                        $mac = $this->normalizeMac($token);
                    }
                    continue;
                }

                if ($this->isBare12Hex($token)) {
                    $formatted = $this->normalizeMac($token);
                    if (!in_array($formatted, $possibleMac, true)) {
                        $possibleMac[] = $formatted;
                    }
                    continue;
                }

                if ($this->isValidEan($token)) {
                    if ($ean === '') {
                        $ean = $token;
                    }
                    continue;
                }

                // CCTV Model prefix detection (Hikvision, CP PLUS, Dahua, Uniview, Ezviz, Imou, TP-Link, Tiandy)
                if (preg_match('/^(?:DS-|iDS-|IDS-|HWI-|HWD-|CP-|DH-|IPC-|UNV-|CS-|EZ-|VIGI|TAPO|TC-|NVR|DVR|XVR|DHI-|HFW|HDW|HDBW)/i', $token)) {
                    if (!in_array($token, $modelTokens, true)) {
                        $modelTokens[] = $token;
                    }
                    if (!in_array($token, $unknownTokens, true)) {
                        $unknownTokens[] = $token;
                    }
                    continue;
                }

                $normSn = $this->normalizeSerial($token);
                if ($normSn !== '' && preg_match('/^[A-Z0-9_-]{3,40}$/', $normSn)) {
                    if (!in_array($normSn, $serialCandidates, true)) {
                        $serialCandidates[] = $normSn;
                    }
                }
                if (!in_array($token, $unknownTokens, true)) {
                    $unknownTokens[] = $token;
                }
            }
        }

        return [
            'ean'               => $ean,
            'mac'               => $mac,
            'possible_mac'      => $possibleMac,
            'serial_candidates' => $serialCandidates,
            'model_tokens'      => $modelTokens,
            'unknown_tokens'    => array_values(array_unique($unknownTokens)),
        ];
    }

    /**
     * Clean raw barcode string by stripping non-printable characters,
     * ISO/IEC 15424 AIM symbology identifiers (e.g. ]C1, ]c1, ]C0, ]e0, ]d1, etc.),
     * and GS1 application identifiers like (21) for serial numbers.
     */
    public static function cleanBarcode(string $raw): string
    {
        $val = trim($raw);
        if ($val === '') {
            return '';
        }

        // Strip ASCII non-printable control characters (FNC1, GS, RS, etc.) while preserving newlines and tabs
        $val = trim((string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $val));

        // Strip ISO/IEC 15424 AIM Symbology Identifier prefix (e.g. ]C1, ]c1, ]C0, ]e0, ]d1, ]Q3, etc.)
        $val = trim((string) preg_replace('/^[\]\[][a-zA-Z0-9]{2}[:\s=_-]*/i', '', $val));

        // Strip GS1 AI (21) for serial number if present
        $val = trim((string) preg_replace('/^\(21\)[:\s=_-]*/i', '', $val));

        return $val;
    }

    public function normalizeSerial(string $serial): string
    {
        $clean = self::cleanBarcode($serial);

        return strtoupper(trim($clean));
    }

    public function normalizeMac(string $mac): string
    {
        $clean = strtoupper(trim((string) preg_replace('/^MAC[:\s=_-]*/i', '', $mac)));
        if ($clean === '') {
            return '';
        }

        $clean = str_replace('-', ':', $clean);
        if (preg_match('/^[0-9A-F]{12}$/', $clean)) {
            return implode(':', str_split($clean, 2));
        }

        return $clean;
    }

    public function isStrictMac(string $value): bool
    {
        return (bool) preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', trim($value));
    }

    public function isBare12Hex(string $value): bool
    {
        return (bool) preg_match('/^[0-9A-Fa-f]{12}$/', trim($value));
    }

    public function isValidEan(string $code): bool
    {
        $code = trim($code);
        if (!preg_match('/^(?:\d{8}|\d{13})$/', $code)) {
            return false;
        }

        $digits = array_map('intval', str_split($code));
        $checkDigit = array_pop($digits);
        $sum = 0;

        foreach (array_reverse($digits) as $index => $digit) {
            $sum += ($index % 2 === 0) ? ($digit * 3) : $digit;
        }

        return ((10 - ($sum % 10)) % 10) === $checkDigit;
    }

    private function findExactProduct(string $ean, array $modelTokens, array $serialCandidates): ?Product
    {
        if ($ean !== '') {
            $eanUpper = strtoupper($ean);
            $product = Product::whereRaw('UPPER(TRIM(ean)) = ?', [$eanUpper])
                ->orWhereRaw('UPPER(TRIM(sku)) = ?', [$eanUpper])
                ->orWhereRaw('UPPER(TRIM(model_no)) = ?', [$eanUpper])
                ->first();
            if ($product) {
                return $product;
            }

            $alias = ProductAlias::where('status', 'approved')
                ->where('alias_type', 'ean')
                ->where('normalized_value', ProductAlias::normalizeValue('ean', $ean))
                ->with('product')
                ->first();
            if ($alias?->product) {
                return $alias->product;
            }
        }

        $allTokens = array_values(array_unique(array_merge($modelTokens, $serialCandidates)));
        foreach ($allTokens as $token) {
            $norm = strtoupper(trim($token));
            if ($norm === '') {
                continue;
            }

            $product = Product::whereRaw('UPPER(TRIM(model_no)) = ?', [$norm])
                ->orWhereRaw('UPPER(TRIM(sku)) = ?', [$norm])
                ->orWhereRaw('UPPER(TRIM(name)) = ?', [$norm])
                ->first();
            if ($product) {
                return $product;
            }

            $alias = ProductAlias::where('status', 'approved')
                ->whereIn('alias_type', ['model_code', 'mpn', 'label_text'])
                ->where('normalized_value', ProductAlias::normalizeValue('model_code', $token))
                ->with('product')
                ->first();
            if ($alias?->product) {
                return $alias->product;
            }
        }

        return null;
    }

    private function findCandidateProducts(array $modelTokens, array $serialCandidates, array $unknownTokens): array
    {
        $terms = array_values(array_unique(array_merge($modelTokens, $unknownTokens, $serialCandidates)));
        $terms = array_filter($terms, fn ($t) => strlen(trim((string) $t)) >= 2);
        if (empty($terms)) {
            return [];
        }

        $query = Product::where('is_active', true);
        $query->where(function ($q) use ($terms) {
            foreach (array_slice($terms, 0, 5) as $idx => $term) {
                $t = trim((string) $term);
                if ($idx === 0) {
                    $q->where('model_no', 'like', "%{$t}%")
                      ->orWhere('name', 'like', "%{$t}%");
                } else {
                    $q->orWhere('model_no', 'like', "%{$t}%")
                      ->orWhere('name', 'like', "%{$t}%");
                }
            }
        });

        return $query->limit(3)
            ->get(['id', 'name', 'brand', 'model_no', 'sku', 'ean', 'category'])
            ->map(fn (Product $p) => [
                'id'       => $p->id,
                'name'     => $p->name,
                'brand'    => $p->brand,
                'model_no' => $p->model_no,
                'sku'      => $p->sku,
                'ean'      => $p->ean,
                'category' => $p->category,
            ])
            ->values()
            ->all();
    }

    private function formatInstalledEquipmentResponse(
        InstalledEquipment $equipment,
        User $user,
        string $ean,
        string $mac,
        array $possibleMac,
        array $serialCandidates,
        array $unknownTokens
    ): array {
        $mfgWarranty = $equipment->manufacturer_warranty_expiry ? Carbon::parse($equipment->manufacturer_warranty_expiry) : null;
        $svcWarranty = $equipment->service_warranty_expiry ? Carbon::parse($equipment->service_warranty_expiry) : null;
        $isUnderWarranty = ($mfgWarranty && $mfgWarranty->isFuture()) || ($svcWarranty && $svcWarranty->isFuture());

        $canSeeCustomerDetails = $user->isOffice() || $this->technicianAssignedToEquipment($user, $equipment);

        $productData = null;
        if ($equipment->product) {
            $productData = [
                'id'              => $equipment->product->id,
                'name'            => $equipment->product->name,
                'brand'           => $equipment->product->brand,
                'model_no'        => $equipment->product->model_no,
                'sku'             => $equipment->product->sku,
                'ean'             => $equipment->product->ean,
                'category'        => $equipment->product->category,
                'warranty_months' => $equipment->product->default_warranty_months,
            ];
            if ($user->isOffice()) {
                $productData['stock_quantity'] = $equipment->product->stock_quantity;
                $productData['unit_price'] = $equipment->product->unit_price;
            }
        }

        $payload = [
            'found'               => true,
            'type'                => 'installed_equipment',
            'id'                  => $equipment->id,
            'product_id'          => $equipment->product_id,
            'product'             => $productData,
            'equipment_name'      => $equipment->equipment_name,
            'serial_number'       => $equipment->serial_number,
            'serial'              => $equipment->serial_number,
            'serial_candidates'   => $serialCandidates,
            'mac_address'         => $equipment->mac_address,
            'mac'                 => $equipment->mac_address ?: $mac,
            'possible_mac'        => $possibleMac,
            'ean'                 => $ean !== '' ? $ean : ($equipment->ean ?? ''),
            'model'               => $equipment->product?->model_no ?: ($equipment->product?->name ?? $equipment->equipment_name),
            'model_no'            => $equipment->product?->model_no ?? '',
            'is_duplicate'        => true,
            'status'              => $equipment->status,
            'location_tag'        => $equipment->location_tag,
            'lead_id'             => $equipment->lead_id,
            'product_name'        => $equipment->product?->name ?? $equipment->equipment_name,
            'product_model'       => $equipment->product?->model_no ?? '',
            'installation_date'   => $equipment->installation_date ? Carbon::parse($equipment->installation_date)->format('d M Y') : null,
            'mfg_warranty_expiry' => $mfgWarranty ? $mfgWarranty->format('d M Y') : 'N/A',
            'svc_warranty_expiry' => $svcWarranty ? $svcWarranty->format('d M Y') : 'N/A',
            'is_under_warranty'   => $isUnderWarranty,
            'show_url'            => route('equipment.show', $equipment),
            'candidates'          => [],
            'unknown_tokens'      => $unknownTokens,
        ];

        if ($canSeeCustomerDetails) {
            $payload['customer_name'] = $equipment->lead?->customer_name ?? 'N/A';
            $payload['customer_phone'] = $equipment->lead?->phone ?? 'N/A';
            $payload['customer_address'] = $equipment->lead?->site_address ?? 'N/A';
            $payload['duplicate_warning'] = "Duplicate serial! S/N '{$equipment->serial_number}' is already registered for {$equipment->lead?->customer_name}" . ($equipment->location_tag ? " ({$equipment->location_tag})" : '') . ". Warn: not saved twice.";
        } else {
            $payload['customer_name'] = 'registered at another site';
            $payload['registered_elsewhere'] = true;
            $payload['duplicate_warning'] = "Duplicate serial! S/N '{$equipment->serial_number}' is registered at another site.";
        }

        return $payload;
    }

    private function technicianAssignedToEquipment(User $user, InstalledEquipment $equipment): bool
    {
        if ($equipment->installationJob && (int) $equipment->installationJob->assigned_technician_id === (int) $user->id) {
            return true;
        }

        if ($equipment->lead_id) {
            return Project::where('lead_id', $equipment->lead_id)
                ->where(function ($q) use ($user) {
                    $q->where('assigned_to', $user->id)
                      ->orWhere('lead_technician_id', $user->id);
                })
                ->exists();
        }

        return false;
    }
}
