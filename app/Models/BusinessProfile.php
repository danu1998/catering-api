<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessProfile extends Model
{
    protected $fillable = [
        'business_name',
        'tagline',
        'description',
        'logo',
        'phone',
        'whatsapp_number',
        'email',
        'address',
        'opening_hours',
        'instagram_url',
        'facebook_url',
        'website_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}