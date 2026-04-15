<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name', 'slug', 'price', 'duration_in_days',
        'cv_limit', 'ai_credits', 'remove_watermark',
        'has_cover_letter', 'priority_support',
        'is_popular', 'is_active', 'description'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cv_limit' => 'integer',
        'ai_credits' => 'integer',
        'duration_in_days' => 'integer',
        'remove_watermark' => 'boolean',
        'has_cover_letter' => 'boolean',
        'priority_support' => 'boolean',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the display price with currency
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2) . ' ر.س';
    }
}
