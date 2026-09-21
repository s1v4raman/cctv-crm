<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAmcRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'lead_id' => ['required', 'exists:leads,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'value' => ['required', 'numeric', 'min:0'],
            'frequency' => ['required', 'in:monthly,quarterly,semi_annually,annually'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
