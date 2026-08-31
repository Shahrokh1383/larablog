<?php

use Carbon\Carbon;
use Modules\Articles\Models\Post;
use Modules\Authors\Http\Resources\AuthorPostResource;
use Shared\Models\User;

test('resource transforms post for author page', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create([
        'user_id' => $user->id,
        'title' => 'Test Post',
        'slug' => 'test-post',
        'excerpt' => 'Short excerpt',
        'featured_image' => 'http://example.com/image.jpg',
        'published_at' => Carbon::parse('2025-01-01 12:00:00'),
        'views' => 42,
        'reading_time' => 5,
    ]);

    $resource = (new AuthorPostResource($post))->toArray(request());

    expect($resource)->toHaveKeys([
        'id', 'slug', 'title', 'excerpt', 'featured_image',
        'published_at', 'views', 'reading_time',
    ]);
    expect($resource['id'])->toBe($post->id);
    expect($resource['slug'])->toBe('test-post');
    expect($resource['title'])->toBe('Test Post');
    expect($resource['excerpt'])->toBe('Short excerpt');
    expect($resource['featured_image'])->toBe('http://example.com/image.jpg');
    expect($resource['published_at'])->toBe('2025-01-01T12:00:00+00:00');
    expect($resource['views'])->toBe(42);
    expect($resource['reading_time'])->toBe(5);
});

test('resource returns null published_at when not set', function () {
    $post = Post::factory()->create(['published_at' => null]);

    $resource = (new AuthorPostResource($post))->toArray(request());

    expect($resource['published_at'])->toBeNull();
});