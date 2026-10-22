<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'menu_id' => [
                'required',
                'integer',
                'exists:menus,id',
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
                'unique:variants,code',
            ],
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'additional_price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'min_pax' => [
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
