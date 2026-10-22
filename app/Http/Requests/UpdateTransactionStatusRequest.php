<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in([
                    'confirmed',
                    'processing',
                    'ready',
                    'delivered',
                    'cancelled',
                ]),
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}