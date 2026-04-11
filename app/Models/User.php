<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'plan_id',
        'ai_credits_balance',
        'cover_letters_balance',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // علاقة المستخدم بالباقة (الخطة)
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    // علاقة المستخدم بسجل عمليات الذكاء الاصطناعي
    public function aiGenerations(): HasMany
    {
        return $this->hasMany(AiGenerationHistory::class);
    }

    // علاقة المستخدم بالسير الذاتية
    public function resumes(): HasMany
    {
        return $this->hasMany(Resume::class);
    }

    // علاقة المستخدم برسائل التحفيز (Cover Letters)
    public function coverLetters(): HasMany
    {
        return $this->hasMany(CoverLetter::class);
    }

    /**
     * التحقق مما إذا كان المستخدم يمتلك باقة فعالة
     */
    public function hasActivePlan(): bool
    {
        // إذا كان plan_id ليس فارغاً، فهذا يعني أن المستخدم مشترك في باقة
        return !is_null($this->plan_id);
    }
}