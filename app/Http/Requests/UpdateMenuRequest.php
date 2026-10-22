<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $menu = $this->route('menu');

        return [
            'category_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:categories,id',
            ],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('menus', 'code')->ignore($menu?->id),
            ],
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:150',
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
            'price' => [
                'sometimes',
                'required',
                'numeric',
                'min:0',
            ],
            'min_pax' => [
                'sometimes',
                'required',
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
