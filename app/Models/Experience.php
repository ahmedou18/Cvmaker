<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Resume;
use App\Models\AiGenerationHistory;

class Experience extends Model
{
    use HasFactory;

    protected $fillable = [
        'resume_id', 'company', 'position', 'start_date', 'end_date', 'is_current', 'description'
    ];
protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];
    // العلاقة مع السيرة الذاتية
    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }

    // ⭐ العلاقة مع سجل الذكاء الاصطناعي
    public function aiGenerations(): MorphMany
    {
        return $this->morphMany(AiGenerationHistory::class, 'generateable');
    }
}