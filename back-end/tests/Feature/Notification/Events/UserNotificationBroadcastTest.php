<?php

use Modules\Notification\Events\UserNotificationBroadcast;
use Illuminate\Broadcasting\PrivateChannel;

test('UserNotificationBroadcast uses private channel and returns payload', function () {
    $userId = 123;
    $payload = [
        'id' => 'notif-1',
        'type' => 'comment',
        'message' => 'New comment',
        'post_id' => 'post-1',
    ];

    $event = new UserNotificationBroadcast($userId, $payload);

    $channels = $event->broadcastOn();
    
    expect($channels)->toHaveCount(1);
    expect($channels[0])->toBeInstanceOf(PrivateChannel::class);
    expect($channels[0]->name)->toBe('private-users.123');
    
    expect($event->broadcastAs())->toBe('notification');
    expect($event->broadcastWith())->toBe($payload);
});