<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $item = $this->route('inventoryItem');

        return [
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('inventory_items', 'code')
                    ->ignore($item?->id),
            ],
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],
            'category' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],
            'unit' => [
                'sometimes',
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
                'sometimes',
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
