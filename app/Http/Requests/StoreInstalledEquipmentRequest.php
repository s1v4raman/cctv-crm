<?php

namespace App\Http\Requests;

use App\Http\Controllers\Api\EquipmentLookupController;
use App\Models\InstalledEquipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInstalledEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->isInternal();
    }

    protected function prepareForValidation(): void
    {
        $lookup = app(EquipmentLookupController::class);

        $serial = $lookup->normalizeSerial((string) $this->input('serial_number', ''));
        $rawMac = trim((string) $this->input('mac_address', ''));
        $mac = $rawMac !== '' ? $lookup->normalizeMac($rawMac) : null;
        $rawEan = trim((string) $this->input('ean', ''));
        $ean = $rawEan !== '' ? $rawEan : null;

        $rawScanInput = $this->input('raw_scan');
        $rawScan = null;
        if (is_array($rawScanInput)) {
            $rawScan = $rawScanInput;
        } elseif (is_string($rawScanInput) && trim($rawScanInput) !== '') {
            $decoded = json_decode($rawScanInput, true);
            $rawScan = is_array($decoded) ? $decoded : ['raw' => trim($rawScanInput)];
        }

        $scanSource = trim((string) $this->input('scan_source', ''));

        $this->merge([
            'serial_number' => $serial,
            'mac_address'   => $mac,
            'ean'           => $ean,
            'raw_scan'      => $rawScan,
            'scan_source'   => $scanSource !== '' ? $scanSource : null,
            'status'        => $this->input('status') ?: 'active',
        ]);
    }

    public function rules(): array
    {
        $productId = $this->input('product_id');
        $allowDuplicate = $this->boolean('allow_duplicate');

        return [
            'lead_id'                      => ['required', 'exists:leads,id'],
            'installation_job_id'          => ['nullable', 'exists:installation_jobs,id'],
            'product_id'                   => ['nullable', 'exists:products,id'],
            'equipment_name'               => ['required', 'string', 'max:255'],
            'serial_number'                => [
                'required',
                'string',
                'max:100',
                function (string $attribute, mixed $value, \Closure $fail) use ($productId, $allowDuplicate): void {
                    if ($allowDuplicate) {
                        return;
                    }

                    $normalized = strtoupper(trim((string) $value));
                    $query = InstalledEquipment::whereRaw('UPPER(TRIM(serial_number)) = ?', [$normalized]);
                    if (!empty($productId)) {
                        $query->where('product_id', $productId);
                    }

                    $existing = $query->first();
                    if ($existing) {
                        $customer = $existing->lead?->customer_name ?? 'another site';
                        $fail("Duplicate serial: Serial '{$normalized}' is already registered for {$customer} ({$existing->equipment_name}).");
                    }
                },
            ],
            'mac_address'                  => [
                'nullable',
                'string',
                'max:50',
                'regex:/^([0-9A-F]{2}:){5}[0-9A-F]{2}$/',
                Rule::unique('installed_equipment', 'mac_address'),
            ],
            'ean'                          => [
                'nullable',
                'string',
                'max:20',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }
                    if (!app(EquipmentLookupController::class)->isValidEan((string) $value)) {
                        $fail('The EAN barcode must be a valid EAN-8 or EAN-13 number with a valid check digit.');
                    }
                },
            ],
            'scan_source'                  => ['nullable', 'in:camera,phone_pairing,scanner_gun,manual'],
            'raw_scan'                     => ['nullable', 'array'],
            'learn_alias_token'            => ['nullable', 'string', 'max:255'],
            'learn_alias_type'             => ['nullable', 'in:ean,model_code,mpn,label_text'],
            'location_tag'                 => ['nullable', 'string', 'max:100'],
            'installation_date'            => ['nullable', 'date'],
            'manufacturer_warranty_expiry' => ['nullable', 'date'],
            'service_warranty_expiry'      => ['nullable', 'date'],
            'status'                       => ['required', 'in:active,under_repair,replaced,decommissioned'],
            'notes'                        => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'lead_id.required'        => 'Please select a customer site or active project.',
            'lead_id.exists'          => 'The selected customer site does not exist.',
            'equipment_name.required' => 'Please enter or select the equipment model name.',
            'serial_number.required'  => 'Hardware serial number (S/N) is required.',
            'mac_address.regex'       => 'MAC address must be in valid AA:BB:CC:DD:EE:FF format.',
            'mac_address.unique'      => 'This MAC address is already registered to another device.',
        ];
    }
}
