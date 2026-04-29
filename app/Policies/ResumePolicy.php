<?php

namespace App\Policies;

use App\Models\Resume;
use App\Models\User;

class ResumePolicy
{
    use Illuminate\Support\Facades\Log;

public function create(User $user): bool
{
    Log::info('Policy create called', [
        'user_id' => $user->id,
        'remaining' => $user->resume_creations_remaining,
        'expired' => $user->plan_expires_at && $user->plan_expires_at->isPast(),
    ]);
    if ($user->plan_expires_at && $user->plan_expires_at->isPast()) return false;
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