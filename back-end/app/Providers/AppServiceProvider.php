<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;

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
        // Attach X-Mailer header to every outgoing email
        Event::listen(MessageSending::class, function (MessageSending $event) {
            $event->message->getHeaders()->addTextHeader(
                'X-Mailer', config('app.name', 'Larablog')
            );
        });
    }
}