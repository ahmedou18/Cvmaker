<?php

namespace App\Policies;

use App\Models\Resume;
use App\Models\User;

class ResumePolicy
{
    /**
     * الحد الأقصى للسير الذاتية للمستخدم
     */
    protected function resolveLimit(User $user): int
    {
        // إذا لم يكن للمستخدم خطة، نمنحه حداً مجانياً = 1 سيرة ذاتية
        if (!$user->plan) {
            return 1;
        }
        
        // إذا كانت الخطة منتهية الصلاحية (في حال وجود عمود plan_expires_at)
        if ($user->plan_expires_at && $user->plan_expires_at->isPast()) {
            return 0;
        }
        
        return $user->plan->cv_limit;
    }

    public function viewAny(User $user): bool
    {
        return $this->resolveLimit($user) > 0;
    }

    public function create(User $user): bool
    {
        $limit = $this->resolveLimit($user);
        if ($limit <= 0) return false;
        
        return $user->resumes()->count() < $limit;
    }
    
    /**
     * السماح بتحميل السيرة فقط إذا كان المستخدم مشتركاً في باقة مدفوعة
     */
    public function download(User $user, Resume $resume): bool
    {
        // يجب أن يكون للمستخدم خطة وسعرها أكبر من صفر
        return $user->plan && $user->plan->price > 0;
    }
}