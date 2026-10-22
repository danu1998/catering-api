<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    protected $fillable = [
        'code',
        'name',
        'category',
        'unit',
        'current_stock',
        'minimum_stock',
        'average_cost',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'current_stock' => 'decimal:3',
            'minimum_stock' => 'decimal:3',
            'average_cost' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(InventoryMutation::class);
    }
}
