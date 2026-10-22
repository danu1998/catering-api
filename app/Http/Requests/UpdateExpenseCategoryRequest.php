<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $category = $this->route('expenseCategory');
        $categoryId = $category instanceof \App\Models\ExpenseCategory
            ? $category->id
            : $category;

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('expense_categories', 'name')
                    ->ignore($categoryId),
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
