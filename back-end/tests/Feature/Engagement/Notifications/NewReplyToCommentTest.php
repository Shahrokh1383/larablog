<?php

use Modules\Engagement\Notifications\NewReplyToComment;
use Modules\Engagement\Models\Comment;
use Shared\Models\User;
use Illuminate\Notifications\AnonymousNotifiable;

test('toDatabase returns correct payload with replier name', function () {
    $user = User::factory()->create(['name' => 'John Doe']);
    $reply = Comment::factory()->byUser($user)->create(['is_approved' => true]);
    $notification = new NewReplyToComment($reply, 'post-slug');

    $payload = $notification->toDatabase(new AnonymousNotifiable());

    expect($payload)->toHaveKeys(['type', 'message', 'is_pending', 'post_id', 'post_slug', 'comment_id', 'reply_id']);
    expect($payload['type'])->toBe('new_reply');
    expect($payload['message'])->toContain('John Doe');
    expect($payload['is_pending'])->toBeFalse();
    expect($payload['comment_id'])->toBe($reply->parent_id);
    expect($payload['reply_id'])->toBe($reply->id);
});