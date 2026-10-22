<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuSchedule extends Model
{
    protected $fillable = [
        'menu_id',
        'schedule_date',
        'price_override',
        'quota',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'schedule_date' => 'date:Y-m-d',
            'price_override' => 'decimal:2',
            'quota' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}