<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CoverLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'target_job_title', 'company_name', 'content'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ⭐ العلاقة مع سجل الذكاء الاصطناعي
    public function aiGenerations(): MorphMany
    {
        return $this->morphMany(AiGenerationHistory::class, 'generateable');
    }
}
