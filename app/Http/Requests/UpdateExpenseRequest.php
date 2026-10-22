<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'expense_category_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('expense_categories', 'id')
                    ->where('is_active', true),
            ],

            'title' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],

            'expense_date' => [
                'sometimes',
                'required',
                'date',
            ],

            'amount' => [
                'sometimes',
                'required',
                'numeric',
                'gt:0',
                'decimal:0,2',
            ],

            'payment_method_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('payment_methods', 'id')
                    ->where('is_active', true),
            ],

            'reference_number' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'notes' => [
                'sometimes',
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }
}