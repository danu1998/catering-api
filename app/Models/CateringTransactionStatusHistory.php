<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CateringTransactionStatusHistory extends Model
{
    protected $fillable = [
        'catering_transaction_id',
        'old_status',
        'new_status',
        'notes',
        'changed_by',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(
            CateringTransaction::class,
            'catering_transaction_id'
        );
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
