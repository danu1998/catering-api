<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStockOpnameItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'physical_stock' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,3',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}
