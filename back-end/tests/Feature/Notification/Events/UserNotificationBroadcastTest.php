<?php

use Modules\Notification\Events\UserNotificationBroadcast;

test('UserNotificationBroadcast uses private channel and returns payload', function () {
    $userId = 123;
    $payload = [
        'id' => 'notif-1',
        'type' => 'comment',
        'message' => 'New comment',
        'post_id' => 'post-1',
    ];

    $event = new UserNotificationBroadcast($userId, $payload);

    expect($event->broadcastOn())->toBe([new \Illuminate\Broadcasting\PrivateChannel('users.123')]);
    expect($event->broadcastAs())->toBe('notification');
    expect($event->broadcastWith())->toBe($payload);
});