<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'catering_transaction_id' => [
                'required',
                'integer',
                'exists:catering_transactions,id',
            ],
            'payment_method_id' => [
                'required',
                'integer',
                'exists:payment_methods,id',
            ],
            'amount' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,2',
            ],
            'reference_number' => [
                'nullable',
                'string',
                'max:100',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }
}
