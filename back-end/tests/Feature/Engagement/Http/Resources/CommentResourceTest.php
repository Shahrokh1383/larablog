<?php

use Modules\Engagement\Http\Resources\CommentResource;
use Modules\Engagement\Models\Comment;
use Shared\Models\User;

test('resource transforms comment for admin with user author', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->byUser($user)->create(['body' => 'Test body']);

    $resource = (new CommentResource($comment))->toArray(request());

    expect($resource)->toHaveKeys(['id', 'post_id', 'parent_id', 'body', 'is_approved', 'author', 'created_at']);
    expect($resource['body'])->toBe('Test body');
    expect($resource['author'])->toHaveKeys(['id', 'name']);
    expect($resource['author']['id'])->toBe($user->id);
});

test('resource transforms comment for guest author', function () {
    $comment = Comment::factory()->asGuest()->create(['body' => 'Guest body']);

    $resource = (new CommentResource($comment))->toArray(request());

    expect($resource['author'])->toHaveKeys(['name', 'email']);
    expect($resource['author']['name'])->toBe($comment->name);
    expect($resource['author']['email'])->toBe($comment->email);
});