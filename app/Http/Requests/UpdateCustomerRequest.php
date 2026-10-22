<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'customer_type' => [
                'sometimes',
                'required',
                Rule::in(['company', 'group', 'individual']),
            ],
            'contact_person' => ['sometimes', 'nullable', 'string', 'max:100'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'email' => ['sometimes', 'nullable', 'email', 'max:150'],
            'address' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'people_count' => ['sometimes', 'integer', 'min:0'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
