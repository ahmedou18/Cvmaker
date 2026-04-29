<?php

namespace App\Observers;

use App\Models\Payment;
use App\Notifications\PlanActivated;
use Illuminate\Support\Facades\DB;

class PaymentObserver
{
    public function updated(Payment $payment)
    {
        if ($payment->isDirty('status') && $payment->status === 'completed') {
            $user = $payment->user;
            $plan = $payment->plan;

            if ($user && $plan) {
                DB::transaction(function () use ($user, $plan) {
                    $user = $user->fresh();
                    $expiresAt = now()->addDays($plan->duration_in_days ?? 30);

                    $updateData = [
                        'plan_id' => $plan->id,
                        'plan_expires_at' => $expiresAt,
                    ];

                    // رصيد الذكاء الاصطناعي (يُستبدل)
                    if ($plan->ai_credits !== null) {
                        $updateData['ai_credits_balance'] = $plan->ai_credits;
                    }

                    // رصيد خطابات التغطية (يُستبدل)
                    $updateData['cover_letters_balance'] = $plan->has_cover_letter ? $plan->cv_limit : 0;

                    // رصيد إنشاء السير (يُضاف إلى الرصيد الحالي)
                    $newBalance = $user->resume_creations_remaining + $plan->cv_limit;
                    $updateData['resume_creations_remaining'] = $newBalance;

                    $user->update($updateData);

                    $user->notify(new PlanActivated($plan->name));
                });
            }
        }
    }
}