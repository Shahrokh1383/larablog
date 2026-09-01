<?php

use Modules\Notification\Listeners\BroadcastNotificationListener;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;

test('service provider listens to NotificationSent event', function () {
    Event::assertListening(
        NotificationSent::class,
        BroadcastNotificationListener::class
    );
});