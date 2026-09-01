<?php

use Modules\ReaderExperience\Http\Resources\RecentlyReadResource;
use Modules\ReaderExperience\Models\PostRead;
use Modules\Articles\Models\Post;
use Shared\Models\User;
use Carbon\Carbon;

test('RecentlyReadResource formats post read with post info', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    $postRead = PostRead::factory()->create([
        'user_id' => $user->id,
        'post_id' => $post->id,
        'read_at' => Carbon::parse('2026-09-01 10:00:00'),
    ]);
    $postInfo = (object)[
        'id' => $post->id,
        'title' => 'Test Post',
        'slug' => 'test-post',
        'featured_image' => null,
        'reading_time' => 7,
    ];
    $postRead->post_info = $postInfo;

    $resource = new RecentlyReadResource($postRead);
    $array = $resource->toArray(request());

    expect($array)->toMatchArray([
        'id' => $postRead->id,
        'read_at' => $postRead->read_at,
        'post' => [
            'id' => $post->id,
            'title' => 'Test Post',
            'slug' => 'test-post',
            'featured_image' => null,
            'reading_time' => 7,
        ],
    ]);
});

test('RecentlyReadResource handles missing post_info gracefully', function () {
    $user = User::factory()->create();
    $postRead = PostRead::factory()->create([
        'user_id' => $user->id,
        'post_id' => Post::factory()->create()->id,
    ]);

    $resource = new RecentlyReadResource($postRead);
    $array = $resource->toArray(request());

    expect($array['post']['id'])->toBeNull();
    expect($array['post']['title'])->toBeNull();
});