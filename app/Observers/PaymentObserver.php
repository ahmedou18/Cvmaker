<?php

namespace App\Observers;

use App\Models\Payment;

class PaymentObserver
{
    public function updated(Payment $payment)
    {
        // إذا تغيرت الحالة إلى 'completed'
        if ($payment->isDirty('status') && $payment->status === 'completed') {
            $user = $payment->user;
            $plan = $payment->plan;

            if ($user && $plan) {
                // تفعيل الباقة وإعادة تعيين المزايا
                $updateData = [
                    'plan_id' => $plan->id,
                ];

                // إعادة تعيين رصيد الذكاء الاصطناعي حسب الباقة
                if ($plan->ai_credits !== null) {
                    $updateData['ai_credits_balance'] = $plan->ai_credits;
                }

                // إعادة تعيين رصيد رسائل التحفيز إذا كانت الباقة تدعمها
                if ($plan->has_cover_letter) {
                    $updateData['cover_letters_balance'] = 1;
                }

                $user->update($updateData);
            }
        }
    }
}