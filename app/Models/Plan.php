<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; //

class Plan extends Model
{
    protected $fillable = [
    'name', 'slug', 'price', 'duration_in_days', 
    'cv_limit', 'ai_credits', 'remove_watermark', 
    'has_cover_letter', 'priority_support', 
    'is_popular', 'is_active', 'description'
];
public function users(): HasMany
{
    return $this->hasMany(User::class);
}
}
