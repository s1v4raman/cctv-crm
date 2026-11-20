<?php

namespace App\Http\Requests;

use App\Helpers\GstHelper;
use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isOffice();
    }

    protected function prepareForValidation(): void
    {
        $gstin = $this->input('gst_number');
        if (!empty($gstin)) {
            $clean = strtoupper(trim((string) $gstin));
            $merge = ['gst_number' => $clean];

            // Auto-derive state if not provided
            if (empty($this->input('state'))) {
                $stateCode = GstHelper::extractStateCode($clean);
                $stateName = GstHelper::getStateName($stateCode);
                if ($stateName) {
                    $merge['state'] = $stateName;
                }
            }

            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:255'],
            'company_name'        => ['nullable', 'string', 'max:255'],
            'contact_person'      => ['nullable', 'string', 'max:255'],
            'email'               => ['nullable', 'email', 'max:255'],
            'phone'               => ['nullable', 'string', 'max:50'],
            'gst_number'          => ['nullable', 'string', 'size:15', 'regex:' . GstHelper::GSTIN_REGEX],
            'address'             => ['nullable', 'string', 'max:2000'],
            'city'                => ['nullable', 'string', 'max:100'],
            'state'               => ['nullable', 'string', 'max:100'],
            'payment_terms'       => ['nullable', 'string', 'max:100'],
            'bank_name'           => ['nullable', 'string', 'max:150'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
            'bank_ifsc'           => ['nullable', 'string', 'max:50'],
            'bank_branch'         => ['nullable', 'string', 'max:150'],
            'upi_id'              => ['nullable', 'string', 'max:150'],
            'payment_link'        => ['nullable', 'string', 'max:500'],
            'categories'          => ['nullable', 'array'],
            'categories.*'        => ['string', 'max:100'],
            'brands_supplied'     => ['nullable', 'string', 'max:500'],
            'is_active'           => ['nullable', 'boolean'],
            'notes'               => ['nullable', 'string'],
        ];
    }
}
