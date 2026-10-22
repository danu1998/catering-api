<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMutation extends Model
{
    protected $fillable = [
        'inventory_item_id',
        'user_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'reference_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'stock_before' => 'decimal:3',
            'stock_after' => 'decimal:3',
        ];
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
