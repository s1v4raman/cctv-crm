<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DeviceLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EquipmentLookupController extends Controller
{
    public function __construct(
        private readonly DeviceLookupService $lookupService
    ) {}

    /**
     * Search and lookup installed equipment or product catalog by single code or multi-code scan session.
     */
    public function lookup(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isInternal()) {
            abort(403, 'Unauthorized action.');
        }

        $scans = [];

        if ($request->has('scans') && is_array($request->input('scans'))) {
            foreach ($request->input('scans') as $item) {
                if (is_array($item) && !empty($item['raw'])) {
                    $scans[] = [
                        'raw'    => (string) $item['raw'],
                        'format' => isset($item['format']) ? (string) $item['format'] : null,
                    ];
                } elseif (is_string($item) && trim($item) !== '') {
                    $scans[] = ['raw' => $item, 'format' => null];
                }
            }
        } else {
            $rawSerial = trim((string) $request->input('serial', ''));
            $rawMac = trim((string) $request->input('mac', ''));
            $rawModel = trim((string) $request->input('model', ''));
            $rawEan = trim((string) $request->input('ean', ''));

            if ($rawSerial !== '') {
                $scans[] = ['raw' => $rawSerial, 'format' => null];
            }
            if ($rawMac !== '') {
                $scans[] = ['raw' => 'MAC:' . $rawMac, 'format' => null];
            }
            if ($rawModel !== '') {
                $scans[] = ['raw' => 'MODEL:' . $rawModel, 'format' => null];
            }
            if ($rawEan !== '') {
                if ($this->isValidEan($rawEan)) {
                    $scans[] = ['raw' => $rawEan, 'format' => 'ean_13'];
                } elseif (empty($scans)) {
                    return response()->json([
                        'found'             => false,
                        'is_duplicate'      => false,
                        'model_not_found'   => true,
                        'invalid_ean'       => true,
                        'message'           => 'Invalid EAN check digit.',
                        'serial'            => '',
                        'serial_number'     => '',
                        'serial_candidates' => [],
                        'mac'               => '',
                        'mac_address'       => '',
                        'possible_mac'      => [],
                        'ean'               => '',
                        'model'             => '',
                        'model_no'          => '',
                        'equipment_name'    => '',
                        'product_name'      => '',
                        'product_id'        => null,
                        'product'           => null,
                        'candidates'        => [],
                        'unknown_tokens'    => [],
                    ]);
                }
            }
        }

        if (empty($scans)) {
            return response()->json([
                'found'   => false,
                'message' => 'Please provide a serial number, MAC address, EAN, or model to search.',
            ], 400);
        }

        return response()->json($this->lookupService->lookup($scans, $user));
    }

    public function normalizeSerial(string $serial): string
    {
        return $this->lookupService->normalizeSerial($serial);
    }

    public function normalizeMac(string $mac): string
    {
        return $this->lookupService->normalizeMac($mac);
    }

    public function isValidEan(string $code): bool
    {
        return $this->lookupService->isValidEan($code);
    }
}
