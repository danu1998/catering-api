<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'code',
        'name',
        'customer_type',
        'contact_person',
        'phone',
        'email',
        'address',
        'people_count',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'people_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(
            CateringTransaction::class,
            'customer_id'
        );
    }
}
