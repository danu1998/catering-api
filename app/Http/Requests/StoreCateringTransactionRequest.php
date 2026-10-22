<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCateringTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_source' => [
                'sometimes',
                'in:pos,website',
            ],

            'customer_id' => [
                'nullable',
                'integer',
                'exists:customers,id',
            ],
            'customer_name' => [
                'required_without:customer_id',
                'nullable',
                'string',
                'max:150',
            ],

            'customer_phone' => [
                'required_without:customer_id',
                'nullable',
                'string',
                'max:30',
            ],

            'customer_email' => [
                'nullable',
                'email',
                'max:150',
            ],

            'company_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            'delivery_address' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'delivery_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],

            'delivery_time' => [
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
                'nullable',
                'string',
                'max:5000',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],

            'items.*.menu_id' => [
                'required',
                'integer',
                'distinct',
                'exists:menus,id',
            ],

            'items.*.quantity' => [
                'required',
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
