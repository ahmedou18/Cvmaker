<?php

namespace App\Observers;

use App\Models\Payment;
use App\Notifications\PlanActivated;

class PaymentObserver
{
    public function updated(Payment $payment)
    {
        if ($payment->isDirty('status') && $payment->status === 'completed') {
            $user = $payment->user;
            $plan = $payment->plan;

            if ($user && $plan) {
                // حساب تاريخ انتهاء الصلاحية بناءً على مدة الباقة
                $expiresAt = now()->addDays($plan->duration_in_days ?? 30);

                // تحديث بيانات المستخدم
                $updateData = [
                    'plan_id' => $plan->id,
                    'plan_expires_at' => $expiresAt,
                ];

                // رصيد الذكاء الاصطناعي
                if ($plan->ai_credits !== null) {
                    $updateData['ai_credits_balance'] = $plan->ai_credits;
                }

                // رصيد خطابات التغطية = عدد السير المسموحة (إذا كانت الباقة تدعمها)
                if ($plan->has_cover_letter) {
                    $updateData['cover_letters_balance'] = $plan->cv_limit; // عدد الرسائل = عدد السير
                } else {
                    $updateData['cover_letters_balance'] = 0;
                }

                $user->update($updateData);

                // إرسال الإشعار
                $user->notify(new PlanActivated($plan->name));
            }
        }
    }
}