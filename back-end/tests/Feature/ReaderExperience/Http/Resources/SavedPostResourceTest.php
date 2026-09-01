<?php

use Modules\ReaderExperience\Http\Resources\SavedPostResource;
use Modules\ReaderExperience\Models\SavedPost;
use Modules\Articles\Models\Post;
use Shared\Models\User;

test('SavedPostResource formats saved post with post info', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    
    $savedPost = SavedPost::create([
        'user_id' => $user->id,
        'post_id' => $post->id,
        'saved_at' => now(),
    ]);
    
    $postInfo = (object)[
        'id' => $post->id,
        'title' => 'Saved Post',
        'slug' => 'saved-post',
        'featured_image' => null,
        'reading_time' => 3,
    ];
    $savedPost->post_info = $postInfo;

    $resource = new SavedPostResource($savedPost);
    $array = $resource->toArray(request());

    expect($array)->toMatchArray([
        'id' => $savedPost->id,
        'saved_at' => $savedPost->saved_at,
        'post' => [
            'id' => $post->id,
            'title' => 'Saved Post',
            'slug' => 'saved-post',
            'featured_image' => null,
            'reading_time' => 3,
        ],
    ]);
});