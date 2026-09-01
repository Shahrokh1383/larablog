<?php

use Modules\Notification\Http\Resources\NotificationResource;
use Illuminate\Notifications\DatabaseNotification;
use Carbon\Carbon;

test('NotificationResource transforms notification correctly', function () {
    $notification = DatabaseNotification::create([
        'id' => (string) \Illuminate\Support\Str::uuid(),
        'type' => 'App\Notifications\TestNotification',
        'notifiable_type' => 'Shared\Models\User',
        'notifiable_id' => 1,
        'data' => [
            'type' => 'comment',
            'message' => 'Someone commented',
            'post_id' => 'post-123',
            'post_slug' => 'my-post',
            'comment_id' => 'comment-456',
            'reply_id' => 'reply-789',
        ],
        'read_at' => null,
        'created_at' => Carbon::parse('2026-09-01 12:00:00'),
        'updated_at' => Carbon::parse('2026-09-01 12:00:00'),
    ]);

    $resource = new NotificationResource($notification);
    $array = $resource->toArray(request());

    expect($array)->toHaveKeys([
        'id', 'type', 'message', 'post_id', 'post_slug',
        'comment_id', 'reply_id', 'read_at', 'created_at',
    ]);
    expect($array['id'])->toBe($notification->id);
    expect($array['type'])->toBe('comment');
    expect($array['message'])->toBe('Someone commented');
    expect($array['post_id'])->toBe('post-123');
    expect($array['post_slug'])->toBe('my-post');
    expect($array['comment_id'])->toBe('comment-456');
    expect($array['reply_id'])->toBe('reply-789');
    expect($array['read_at'])->toBeNull();
    expect($array['created_at'])->toBe('2026-09-01T12:00:00+00:00');
});

test('NotificationResource handles missing data fields', function () {
    $notification = DatabaseNotification::create([
        'id' => (string) \Illuminate\Support\Str::uuid(),
        'type' => 'App\Notifications\TestNotification',
        'notifiable_type' => 'Shared\Models\User',
        'notifiable_id' => 1,
        'data' => [], // empty data
        'read_at' => Carbon::parse('2026-09-01 12:00:00'),
        'created_at' => Carbon::parse('2026-09-01 10:00:00'),
        'updated_at' => Carbon::parse('2026-09-01 10:00:00'),
    ]);

    $resource = new NotificationResource($notification);
    $array = $resource->toArray(request());

    expect($array['type'])->toBeNull();
    expect($array['message'])->toBeNull();
    expect($array['post_id'])->toBeNull();
    expect($array['post_slug'])->toBeNull();
    expect($array['comment_id'])->toBeNull();
    expect($array['reply_id'])->toBeNull();
    expect($array['read_at'])->toBe('2026-09-01T12:00:00+00:00');
    expect($array['created_at'])->toBe('2026-09-01T10:00:00+00:00');
});