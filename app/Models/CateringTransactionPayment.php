<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CateringTransactionPayment extends Model
{
    protected $fillable = [
        'catering_transaction_id',
        'payment_method_id',
        'amount',
        'payment_status',
        'reference_number',
        'notes',
        'paid_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(
            CateringTransaction::class,
            'catering_transaction_id'
        );
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(
            PaymentMethod::class,
            'payment_method_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}