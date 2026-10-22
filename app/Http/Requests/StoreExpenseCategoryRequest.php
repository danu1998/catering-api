<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:expense_categories,name',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}