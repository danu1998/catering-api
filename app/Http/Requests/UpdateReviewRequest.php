<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'catering_transaction_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:catering_transactions,id',
            ],

            'customer_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:customers,id',
            ],

            'customer_name' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],

            'rating' => [
                'sometimes',
                'required',
                'integer',
                'between:1,5',
            ],

            'comment' => [
                'sometimes',
                'nullable',
                'string',
                'max:5000',
            ],

            'is_visible' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' =>
                'Nama pelanggan wajib diisi.',

            'rating.required' =>
                'Rating wajib diisi.',

            'rating.between' =>
                'Rating harus bernilai antara 1 sampai 5.',

            'catering_transaction_id.exists' =>
                'Transaksi catering tidak ditemukan.',

            'customer_id.exists' =>
                'Pelanggan tidak ditemukan.',
        ];
    }
}
