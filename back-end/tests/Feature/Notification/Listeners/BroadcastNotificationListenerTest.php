<?php

use Modules\Notification\Listeners\BroadcastNotificationListener;
use Modules\Notification\Events\UserNotificationBroadcast;
use Shared\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use Carbon\Carbon;

beforeEach(function () {
    Event::fake([UserNotificationBroadcast::class]);
});

test('handle broadcasts UserNotificationBroadcast with correct payload for database channel and User notifiable', function () {
    $user = User::factory()->create();

    // Create a real database notification
    $dbNotification = DatabaseNotification::create([
        'id' => (string) \Illuminate\Support\Str::uuid(),
        'type' => 'App\Notifications\TestNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => [
            'type' => 'comment',
            'message' => 'Hello',
            'post_id' => 'post-1',
            'post_slug' => 'some-post',
            'comment_id' => 'comment-1',
            'reply_id' => null,
        ],
        'read_at' => null,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ]);

    $notification = new class {
        public $id;
    };
    $notification->id = $dbNotification->id;

    $event = new NotificationSent(
        $user,
        $notification,
        'database',
        null // response
    );

    $listener = new BroadcastNotificationListener();
    $listener->handle($event);

    Event::assertDispatched(UserNotificationBroadcast::class, function ($broadcastEvent) use ($user, $dbNotification) {
        return $broadcastEvent->userId === $user->id &&
            $broadcastEvent->notificationData['id'] === $dbNotification->id &&
            $broadcastEvent->notificationData['type'] === 'comment' &&
            $broadcastEvent->notificationData['message'] === 'Hello' &&
            $broadcastEvent->notificationData['post_id'] === 'post-1' &&
            $broadcastEvent->notificationData['post_slug'] === 'some-post' &&
            $broadcastEvent->notificationData['comment_id'] === 'comment-1' &&
            $broadcastEvent->notificationData['reply_id'] === null &&
            $broadcastEvent->notificationData['read_at'] === null;
    });
});

test('handle does nothing if channel is not database', function () {
    $user = User::factory()->create();
    $notification = new class { public $id = 'fake-id'; };
    $event = new NotificationSent($user, $notification, 'mail', null);

    $listener = new BroadcastNotificationListener();
    $listener->handle($event);

    Event::assertNotDispatched(UserNotificationBroadcast::class);
});

test('handle does nothing if notifiable is not User', function () {
    $notifiable = new class {}; // not a User
    $notification = new class { public $id = 'fake-id'; };
    $event = new NotificationSent($notifiable, $notification, 'database', null);

    $listener = new BroadcastNotificationListener();
    $listener->handle($event);

    Event::assertNotDispatched(UserNotificationBroadcast::class);
});

test('handle does nothing if database notification not found', function () {
    $user = User::factory()->create();
    $notification = new class { public $id = 'non-existent-id'; };
    $event = new NotificationSent($user, $notification, 'database', null);

    $listener = new BroadcastNotificationListener();
    $listener->handle($event);

    Event::assertNotDispatched(UserNotificationBroadcast::class);
});