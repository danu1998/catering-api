<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'category_id',
        'code',
        'name',
        'slug',
        'description',
        'image',
        'price',
        'min_pax',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'min_pax' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(function () {
            return $this->image
                ? asset('storage/' . $this->image)
                : null;
        });
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(MenuSchedule::class);
    }
}
