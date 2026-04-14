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
                // تحديث بيانات المستخدم بالباقة الجديدة
                $updateData = ['plan_id' => $plan->id];

                if ($plan->ai_credits !== null) {
                    $updateData['ai_credits_balance'] = $plan->ai_credits;
                }

                if ($plan->has_cover_letter) {
                    $updateData['cover_letters_balance'] = 1;
                }

                $user->update($updateData);

                // إرسال الإشعار
                $user->notify(new PlanActivated($plan->name));
            }
        }
    }
}