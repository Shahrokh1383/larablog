<?php

use Modules\Engagement\Http\Resources\CommentPublicResource;
use Modules\Engagement\Models\Comment;
use Shared\Models\User;

test('public resource includes avatar and replies information', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->byUser($user)->approved()->create(['body' => 'Public comment']);
    $comment->setAttribute('avatar', 'http://example.com/avatar.png');
    $comment->setAttribute('replies_count', 5);
    $comment->setAttribute('replies_has_more', true);
    $comment->load('replies');

    $resource = (new CommentPublicResource($comment))->toArray(request());

    expect($resource)->toHaveKeys(['id', 'post_id', 'parent_id', 'body', 'is_approved', 'author', 'replies', 'replies_count', 'replies_has_more', 'created_at']);
    expect($resource['author'])->toHaveKeys(['id', 'name', 'avatar']);
    expect($resource['author']['avatar'])->toBe('http://example.com/avatar.png');
    expect($resource['replies_count'])->toBe(5);
    expect($resource['replies_has_more'])->toBeTrue();
});

test('guest comment returns null avatar and guest name', function () {
    $comment = Comment::factory()->asGuest()->approved()->create();
    $comment->setAttribute('avatar', null);
    $comment->setAttribute('replies_count', 0);
    $comment->setAttribute('replies_has_more', false);

    $resource = (new CommentPublicResource($comment))->toArray(request());

    expect($resource['author']['avatar'])->toBeNull();
    expect($resource['author']['name'])->toBe($comment->name);
});