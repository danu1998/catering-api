<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_name' => [
                'required',
                'string',
                'max:150',
            ],
            'tagline' => [
                'nullable',
                'string',
                'max:200',
            ],
            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'logo' => [
                'nullable',
                'string',
                'max:2048',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],
            'whatsapp_number' => [
                'nullable',
                'string',
                'max:30',
            ],
            'email' => [
                'nullable',
                'email',
                'max:150',
            ],
            'address' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'opening_hours' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'instagram_url' => [
                'nullable',
                'url',
                'max:255',
            ],
            'facebook_url' => [
                'nullable',
                'url',
                'max:255',
            ],
            'website_url' => [
                'nullable',
                'url',
                'max:255',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}