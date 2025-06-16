<?php

namespace App\Providers;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Models\Assessment;
use App\Policies\AssessmentPolicy;

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
        Schema::defaultStringLength(191); //
        // Customize the password reset URL
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            // Get the base URL of your Laravel application
            $appBaseUrl = config('app.frontend_url', config('app.url')); // Uses FRONTEND_URL or APP_URL

            // Path to your login page which will also handle password reset form
            $resetPath = '/login.html'; // <--- Updated this line

            return $appBaseUrl . $resetPath . '?token=' . $token . '&email=' . urlencode($notifiable->getEmailForPasswordReset());
        });
    }
}
