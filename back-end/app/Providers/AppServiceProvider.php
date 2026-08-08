<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\Identity\Models\User as IdentityUser;
use Shared\Models\User as SharedUser;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register module service providers from config/modules.php
        foreach (config('modules.enabled', []) as $provider) {
            $this->app->register($provider);
        }

        if ($this->app->environment('testing')) {
            $this->app->singleton(\Tests\Support\SmtpSinkService::class, function () {
                return new \Tests\Support\SmtpSinkService();
            });
        }
    }

    public function boot(): void
    {
        Event::listen(MessageSending::class, function (MessageSending $event) {
            $event->message->getHeaders()->addTextHeader(
                'X-Mailer', config('app.name', 'Larablog')
            );
        });

        // Forces Laravel to use the Shared Kernel class name for all DB relations,
        // preventing mismatches between Auth (Identity) and other modules (Engagement).
        Relation::morphMap([
            SharedUser::class => IdentityUser::class,
        ]);
    }
}