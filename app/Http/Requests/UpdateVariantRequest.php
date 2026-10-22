<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $variant = $this->route('variant');

        return [
            'menu_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:menus,id',
            ],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('variants', 'code')
                    ->ignore($variant?->id),
            ],
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'image' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'additional_price' => [
                'sometimes',
                'required',
                'numeric',
                'min:0',
            ],
            'min_pax' => [
                'sometimes',
                'nullable',
                'integer',
                'min:1',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
            'sort_order' => [
                'sometimes',
                'integer',
                'min:0',
            ],
        ];
    }
}
