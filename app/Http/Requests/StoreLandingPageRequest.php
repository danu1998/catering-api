<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLandingPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => [
                'required',
                'string',
                'max:150',
            ],

            'customer_phone' => [
                'required',
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
                'required',
                'string',
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
                'nullable',
                'numeric',
                'min:0',
            ],

            'discount_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.menu_id' => [
                'required',
                'integer',
                'exists:menus,id',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'items.*.notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' =>
            'Nama pelanggan wajib diisi.',

            'customer_phone.required' =>
            'Nomor telepon wajib diisi.',

            'delivery_address.required' =>
            'Alamat pengiriman wajib diisi.',

            'delivery_date.required' =>
            'Tanggal pengiriman wajib diisi.',

            'delivery_date.after_or_equal' =>
            'Tanggal pengiriman tidak boleh sebelum hari ini.',

            'items.required' =>
            'Minimal harus ada satu menu yang dipesan.',

            'items.*.menu_id.exists' =>
            'Menu yang dipilih tidak ditemukan.',
        ];
    }
}
