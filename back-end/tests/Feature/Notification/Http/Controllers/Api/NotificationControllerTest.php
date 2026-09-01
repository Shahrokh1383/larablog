<?php

use Modules\Notification\Services\NotificationService;
use Shared\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Carbon\Carbon;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'sanctum');
});

test('index returns only unread notifications of authenticated user, latest first, max 10', function () {
    // Create 12 unread notifications (oldest first)
    $notifications = [];
    for ($i = 0; $i < 12; $i++) {
        $notifications[] = DatabaseNotification::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'data' => [
                'type' => 'comment',
                'message' => "Notification {$i}",
                'post_id' => 'post-' . $i,
            ],
            'read_at' => null,
            'created_at' => Carbon::now()->subMinutes(12 - $i), // ascending age
            'updated_at' => Carbon::now()->subMinutes(12 - $i),
        ]);
    }

    // Create one read notification (should be excluded)
    DatabaseNotification::create([
        'id' => (string) \Illuminate\Support\Str::uuid(),
        'type' => 'App\Notifications\TestNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $this->user->id,
        'data' => ['type' => 'comment', 'message' => 'Read one'],
        'read_at' => Carbon::now(),
        'created_at' => Carbon::now()->subMinutes(1),
        'updated_at' => Carbon::now()->subMinutes(1),
    ]);

    $response = $this->getJson('/api/notifications');

    $response->assertOk()
        ->assertJsonCount(10, 'data')
        ->assertJsonPath('data.0.message', 'Notification 11'); // newest first
});

test('markSingleAsRead marks a specific notification as read', function () {
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

    $response = $this->postJson("/api/notifications/{$notification->id}/read");

    $response->assertOk()
        ->assertJson(['message' => 'Notification marked as read.']);

    $this->assertNotNull($notification->fresh()->read_at);
});

test('markSingleAsRead returns success even if notification not found', function () {
    $response = $this->postJson('/api/notifications/non-existent-id/read');

    $response->assertOk()
        ->assertJson(['message' => 'Notification marked as read.']);
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

    $response = $this->postJson('/api/notifications/mark-as-read');

    $response->assertOk()
        ->assertJson(['message' => 'All notifications marked as read.']);

    $this->assertSame(0, $this->user->unreadNotifications()->count());
});