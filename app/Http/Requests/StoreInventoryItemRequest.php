<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'nullable',
                'string',
                'max:50',
                'unique:inventory_items,code',
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'category' => [
                'nullable',
                'string',
                'max:100',
            ],
            'unit' => [
                'required',
                'string',
                'max:30',
            ],
            'current_stock' => [
                'sometimes',
                'numeric',
                'min:0',
            ],
            'minimum_stock' => [
                'sometimes',
                'numeric',
                'min:0',
            ],
            'average_cost' => [
                'sometimes',
                'numeric',
                'min:0',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
