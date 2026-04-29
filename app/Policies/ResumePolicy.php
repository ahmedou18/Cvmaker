<?php

namespace App\Policies;

use App\Models\Resume;
use App\Models\User;
use Illuminate\Support\Facades\Log; // ← استيراد صحيح

class ResumePolicy
{
    public function create(User $user): bool
    {
        Log::info('Policy create called', [
            'user_id' => $user->id,
            'remaining' => $user->resume_creations_remaining,
            'expired' => $user->plan_expires_at && $user->plan_expires_at->isPast(),
        ]);

        // لا يمكن الإنشاء إذا كانت الباقة منتهية
        if ($user->plan_expires_at && $user->plan_expires_at->isPast()) {
            return false;
        }

        // يُسمح فقط إذا كان هناك رصيد متبقي
        return $user->resume_creations_remaining > 0;
    }

    public function download(User $user, Resume $resume): bool
    {
        return $user->plan && $user->plan->price > 0;
    }

    protected function resolveLimit(User $user): int
    {
        return $user->plan?->cv_limit ?? 0;
    }

    public function viewAny(User $user): bool
    {
        return $this->resolveLimit($user) > 0;
    }
}