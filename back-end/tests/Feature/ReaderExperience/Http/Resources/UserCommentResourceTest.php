<?php

use Modules\ReaderExperience\Http\Resources\UserCommentResource;
use Modules\Engagement\Models\Comment;
use Modules\Articles\Models\Post;
use Shared\Models\User;

test('UserCommentResource formats comment with post info', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    $comment = Comment::factory()->byUser($user)->approved()->create([
        'post_id' => $post->id,
    ]);
    $comment->post_slug = 'test-post';
    $comment->post_title = 'Test Post';

    $resource = new UserCommentResource($comment);
    $array = $resource->toArray(request());

    expect($array)->toMatchArray([
        'id' => $comment->id,
        'body' => $comment->body,
        'created_at' => $comment->created_at,
        'post' => [
            'slug' => 'test-post',
            'title' => 'Test Post',
        ],
    ]);
});