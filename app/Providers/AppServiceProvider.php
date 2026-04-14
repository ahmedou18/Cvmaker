<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // أضفنا هذا السطر

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
     
    }
}