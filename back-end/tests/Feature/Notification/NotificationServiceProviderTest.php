<?php

use Modules\Notification\Listeners\BroadcastNotificationListener;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;

test('service provider listens to NotificationSent event', function () {
    $dispatcher = Event::getFacadeRoot();
    $listeners = $dispatcher->getListeners(NotificationSent::class);

    $found = collect($listeners)->contains(function ($listener) {
        $reflection = new \ReflectionFunction($listener);
        $staticVars = $reflection->getStaticVariables();
        
        return isset($staticVars['listener']) && $staticVars['listener'] === BroadcastNotificationListener::class;
    });

    expect($found)->toBeTrue('BroadcastNotificationListener is not registered for NotificationSent event.');
});