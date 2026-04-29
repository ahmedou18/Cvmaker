<?php

namespace App\Policies;

use App\Models\Resume;
use App\Models\User;

class ResumePolicy
{
    public function create(User $user): bool
    {
        // إذا كانت الخطة منتهية الصلاحية، لا يمكن الإنشاء
        if ($user->plan_expires_at && $user->plan_expires_at->isPast()) {
            return false;
        }

        // يجب أن يكون رصيد الإنشاءات أكبر من 0
        return $user->resume_creations_remaining > 0;
    }

    public function download(User $user, Resume $resume): bool
    {
        // السماح بالتحميل فقط للمستخدمين ذوي الخطة المدفوعة
        return $user->plan && $user->plan->price > 0;
    }

    // دوال إضافية (قديمة) للتوافق مع بقية الكود
    protected function resolveLimit(User $user): int
    {
        return $user->plan?->cv_limit ?? 0;
    }

    public function viewAny(User $user): bool
    {
        return $this->resolveLimit($user) > 0;
    }
}