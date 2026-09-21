<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateJobRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
         return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'scheduled_date' => ['nullable', 'date', 'after_or_equal:today'],
            'assigned_technician_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'in:pending,scheduled,assigned,in_progress,completed,cancelled'],
            'installation_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
