<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
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

        RateLimiter::for('public-taxonomy', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });
    }
}