<?php

use Modules\Notification\Services\NotificationService;
use Shared\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Carbon\Carbon;

beforeEach(function () {
    $this->service = new NotificationService();
    $this->user = User::factory()->create();
});

test('getUnreadNotifications returns only unread, latest first, max 10', function () {
    // Create 12 unread notifications with increasing created_at
    $notifications = [];
    for ($i = 0; $i < 12; $i++) {
        $notifications[] = DatabaseNotification::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'data' => ['type' => 'comment', 'message' => "Notification {$i}"],
            'read_at' => null,
            'created_at' => Carbon::now()->subMinutes(12 - $i),
            'updated_at' => Carbon::now()->subMinutes(12 - $i),
        ]);
    }

    // One read notification
    DatabaseNotification::create([
        'id' => (string) \Illuminate\Support\Str::uuid(),
        'type' => 'App\Notifications\TestNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $this->user->id,
        'data' => ['type' => 'comment', 'message' => 'Read one'],
        'read_at' => Carbon::now(),
        'created_at' => Carbon::now()->subMinute(),
        'updated_at' => Carbon::now()->subMinute(),
    ]);

    $result = $this->service->getUnreadNotifications($this->user);

    expect($result)->toHaveCount(10);
    expect($result->first()->data['message'])->toBe('Notification 11');
});

test('markAsRead marks the given unread notification as read', function () {
    $notification = DatabaseNotification::create([
        'id' => (string) \Illuminate\Support\Str::uuid(),
        'type' => 'App\Notifications\TestNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $this->user->id,
        'data' => ['type' => 'comment', 'message' => 'Mark me'],
        'read_at' => null,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ]);

    $this->service->markAsRead($this->user, $notification->id);

    $this->assertNotNull($notification->fresh()->read_at);
});

test('markAsRead does nothing if notification does not belong to user', function () {
    $otherUser = User::factory()->create();
    $notification = DatabaseNotification::create([
        'id' => (string) \Illuminate\Support\Str::uuid(),
        'type' => 'App\Notifications\TestNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $otherUser->id,
        'data' => ['type' => 'comment', 'message' => 'Not yours'],
        'read_at' => null,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ]);

    $this->service->markAsRead($this->user, $notification->id);

    $this->assertNull($notification->fresh()->read_at);
});

test('markAllAsRead marks all unread notifications as read', function () {
    DatabaseNotification::create([
        'id' => (string) \Illuminate\Support\Str::uuid(),
        'type' => 'App\Notifications\TestNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $this->user->id,
        'data' => ['type' => 'comment', 'message' => 'Unread 1'],
        'read_at' => null,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ]);
    DatabaseNotification::create([
        'id' => (string) \Illuminate\Support\Str::uuid(),
        'type' => 'App\Notifications\TestNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $this->user->id,
        'data' => ['type' => 'comment', 'message' => 'Unread 2'],
        'read_at' => null,
        'created_at' => Carbon::now()->subMinute(),
        'updated_at' => Carbon::now()->subMinute(),
    ]);

    $this->service->markAllAsRead($this->user);

    $this->assertSame(0, $this->user->unreadNotifications()->count());
});