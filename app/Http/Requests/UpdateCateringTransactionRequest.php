<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCateringTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],

            'customer_phone' => [
                'sometimes',
                'required',
                'string',
                'max:30',
            ],

            'customer_email' => [
                'sometimes',
                'nullable',
                'email',
                'max:150',
            ],

            'company_name' => [
                'sometimes',
                'nullable',
                'string',
                'max:150',
            ],

            'delivery_address' => [
                'sometimes',
                'required',
                'string',
                'max:5000',
            ],

            'delivery_date' => [
                'sometimes',
                'required',
                'date',
                'after_or_equal:today',
            ],

            'delivery_time' => [
                'sometimes',
                'nullable',
                'string',
                'max:30',
            ],

            'delivery_fee' => [
                'sometimes',
                'numeric',
                'min:0',
                'decimal:0,2',
            ],

            'discount_amount' => [
                'sometimes',
                'numeric',
                'min:0',
                'decimal:0,2',
            ],

            'notes' => [
                'sometimes',
                'nullable',
                'string',
                'max:5000',
            ],

            'items' => [
                'sometimes',
                'array',
                'min:1',
                'max:100',
            ],

            'items.*.menu_id' => [
                'required_with:items',
                'integer',
                'distinct',
                'exists:menus,id',
            ],

            'items.*.quantity' => [
                'required_with:items',
                'integer',
                'min:1',
                'max:100000',
            ],

            'items.*.notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
