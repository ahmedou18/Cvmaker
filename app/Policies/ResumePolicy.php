<?php

namespace App\Policies;

use App\Models\Resume;
use App\Models\User;
use Illuminate\Support\Facades\Log; // ← استيراد صحيح

class ResumePolicy
{
    public function create(User $user): bool
{
    if ($user->plan_expires_at && $user->plan_expires_at->isPast()) {
        return false;
    }

    $plan = $user->plan;
    if (!$plan) {
        return false;
    }

    // لو كان الحقل 0، نحسب الرصيد الحقيقي من الباقة
    if ($user->resume_creations_remaining <= 0) {
        $used = $user->resumes()->count();
        $remaining = max(0, $plan->cv_limit - $used);
        return $remaining > 0;
    }

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