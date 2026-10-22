<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'catering_transaction_id',
        'customer_id',
        'customer_name',
        'rating',
        'comment',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_visible' => 'boolean',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(
            CateringTransaction::class,
            'catering_transaction_id'
        );
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            Customer::class,
            'customer_id'
        );
    }
}
