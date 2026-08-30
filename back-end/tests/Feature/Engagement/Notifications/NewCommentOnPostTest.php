<?php

use Modules\Engagement\Notifications\NewCommentOnPost;
use Modules\Engagement\Models\Comment;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

test('toDatabase returns correct payload', function () {
    $comment = Comment::factory()->create(['is_approved' => false]);
    $notification = new NewCommentOnPost($comment, 'Post Title', 'post-slug');

    $payload = $notification->toDatabase(new AnonymousNotifiable());

    expect($payload)->toHaveKeys(['type', 'message', 'is_pending', 'post_id', 'post_slug', 'comment_id']);
    expect($payload['type'])->toBe('new_comment');
    expect($payload['is_pending'])->toBeTrue();
    expect($payload['post_id'])->toBe($comment->post_id);
    expect($payload['comment_id'])->toBe($comment->id);
});