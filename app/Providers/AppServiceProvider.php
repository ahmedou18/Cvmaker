<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // أضفنا هذا السطر
use Illuminate\Support\Facades\Gate;
use App\Models\Resume;
use App\Models\CoverLetter;
use App\Policies\ResumePolicy;
use App\Policies\CoverLetterPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // إجبار النظام على استخدام رابط Codespaces و https
        URL::forceScheme('https');
        URL::forceRootUrl(env('APP_URL'));

        // أضف هذين السطرين
        \App\Models\Payment::observe(\App\Observers\PaymentObserver::class);

        // Register policies
        Gate::policy(Resume::class, ResumePolicy::class);
        Gate::policy(CoverLetter::class, CoverLetterPolicy::class);
    }
}