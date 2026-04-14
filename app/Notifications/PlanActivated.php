<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PlanActivated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $planName;

    public function __construct($planName)
    {
        $this->planName = $planName;
    }

    public function via($notifiable)
    {
        return ['database']; // تخزين في قاعدة البيانات فقط
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "تم تفعيل باقتك {$this->planName} بنجاح! يمكنك الآن استخدام جميع مميزات الباقة.",
            'plan_name' => $this->planName,
        ];
    }

    // اختياري: إرسال بريد إلكتروني أيضاً
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('تم تفعيل باقتك في CVmaker')
            ->line("تهانينا! تم تفعيل باقتك {$this->planName}.")
            ->action('الذهاب إلى لوحة التحكم', url('/dashboard'))
            ->line('شكراً لاستخدامك موقعنا.');
    }
}