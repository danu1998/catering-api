<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Variant extends Model
{
    protected $fillable = [
        'menu_id',
        'code',
        'name',
        'slug',
        'description',
        'image',
        'additional_price',
        'min_pax',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'additional_price' => 'decimal:2',
            'min_pax' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(function () {
            return $this->image
                ? asset('storage/' . $this->image)
                : null;
        });
    }
}