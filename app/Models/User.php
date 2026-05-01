<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'plan_id',
        'plan_expires_at',               // ✅ مطلوب لتحديث تاريخ انتهاء الباقة
        'ai_credits_balance',
        'cover_letters_balance',
        'resume_creations_remaining',    // ✅ مطلوب لتحديث رصيد إنشاء السير
        'support_code',
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
            'plan_expires_at' => 'datetime',     // ✅ إضافة cast ليتعامل Laravel مع التاريخ بشكل صحيح
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
        return !is_null($this->plan_id);
    }

    /**
     * توليد رمز دعم فريد مكون من 6 أرقام
     */
    public function generateSupportCode(): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->update(['support_code' => $code]);
        return $code;
    }

    /**
     * الحصول على رمز الدعم الحالي أو توليد واحد جديد
     */
    public function getOrCreateSupportCode(): string
    {
        if (is_null($this->support_code)) {
            return $this->generateSupportCode();
        }
        return $this->support_code;
    }

    /**
     * التحقق مما إذا كان المستخدم مؤهلاً للدعم ذو الأولوية
     */
    public function hasPrioritySupport(): bool
    {
        return $this->plan?->priority_support ?? false;
    }

    /**
     * التحقق مما إذا كان يمكن للمستخدم إنشاء خطابات تغطية
     */
    public function canCreateCoverLetters(): bool
    {
        return $this->plan?->has_cover_letter ?? false;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->email === 'kmed2498@gmail.com';
    }
}