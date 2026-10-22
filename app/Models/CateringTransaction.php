<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CateringTransaction extends Model
{
    protected $fillable = [
        'transaction_number',
        'order_source',
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'company_name',
        'delivery_address',
        'delivery_date',
        'delivery_time',
        'total_pax',
        'subtotal',
        'delivery_fee',
        'discount_amount',
        'total_amount',
        'order_status',
        'payment_status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date:Y-m-d',
            'total_pax' => 'integer',
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(CateringTransactionItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(
            CateringTransactionStatusHistory::class,
            'catering_transaction_id'
        )->orderBy('created_at');
    }
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
