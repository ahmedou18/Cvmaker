<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGenerationHistory extends Model
{
    use HasFactory;

    protected $table = 'ai_generations_history';

    protected $fillable = [
        'user_id',
        'generateable_type',
        'generateable_id',
        'raw_input',
        'enhancement_options',
        'generated_output',
        'credits_used',
    ];

    protected $casts = [
        'enhancement_options' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // علاقة Polymorphic للربط مع الخبرات أو التعليم أو الملخص
    public function generateable(): MorphTo
    {
        return $this->morphTo();
    }
}