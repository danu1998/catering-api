<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'expense_number',
        'expense_category_id',
        'title',
        'expense_date',
        'amount',
        'payment_method_id',
        'reference_number',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date:Y-m-d',
            'amount' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            ExpenseCategory::class,
            'expense_category_id'
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
