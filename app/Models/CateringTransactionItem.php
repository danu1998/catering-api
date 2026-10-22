<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CateringTransactionItem extends Model
{
    protected $fillable = [
        'catering_transaction_id',
        'menu_id',
        'menu_name',
        'quantity',
        'unit_price',
        'subtotal',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(
            CateringTransaction::class,
            'catering_transaction_id'
        );
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
