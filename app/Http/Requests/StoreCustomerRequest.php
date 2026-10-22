<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'customer_type' => [
                'required',
                Rule::in(['company', 'group', 'individual']),
            ],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:5000'],
            'people_count' => ['sometimes', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}