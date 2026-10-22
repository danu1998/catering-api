<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryMutationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inventory_item_id' => [
                'required',
                'integer',
                'exists:inventory_items,id',
            ],

            'type' => [
                'required',
                Rule::in(['in', 'out', 'adjustment']),
            ],

            'quantity' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,3',
            ],

            'reference_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}
