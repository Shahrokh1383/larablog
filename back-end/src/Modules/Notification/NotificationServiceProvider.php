<?php

namespace Modules\Notification;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Notifications\Events\NotificationSent;
use Modules\Notification\Listeners\BroadcastNotificationListener;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Bridge: Listen to Laravel's native notification event and broadcast to Reverb
        Event::listen(NotificationSent::class, BroadcastNotificationListener::class);
        
    }
}