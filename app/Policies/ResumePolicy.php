<?php

namespace App\Policies;

use App\Models\Resume;
use App\Models\User;

class ResumePolicy
{
    /**
     * Determine the maximum number of resumes a user can have
     * based on their plan's cv_limit. Defaults to 0 if no plan.
     */
    protected function resolveLimit(User $user): int
    {
        // إذا كانت الباقة منتهية الصلاحية، لا يحق له إنشاء سير جديدة
        if ($user->plan_expires_at && $user->plan_expires_at->isPast()) {
            return 0;
        }
        return $user->plan?->cv_limit ?? 0;
    }

    /**
     * Determine if the user can view any resumes.
     */
    public function viewAny(User $user): bool
    {
        return $this->resolveLimit($user) > 0;
    }

    /**
     * Determine if the user can create a new resume.
     */
    public function create(User $user): bool
    {
        $limit = $this->resolveLimit($user);

        if ($limit <= 0) {
            return false;
        }

        return $user->resumes()->count() < $limit;
    }
}