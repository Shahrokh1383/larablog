<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->environment('testing')) {
            $this->app->singleton(\Tests\Support\SmtpSinkService::class, function () {
                return new \Tests\Support\SmtpSinkService();
            });
        }
    }

    public function boot(): void
    {
        Mail::mailer('smtp')->alwaysWithHeaders([
            'X-Mailer' => config('app.name'),
        ]);
    }
}