<?php

use Modules\Articles\Models\Post;
use Modules\Articles\Http\Resources\PostResource;
use Modules\Articles\Http\Resources\PostPublicResource;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

test('PostResource output shape', function () {
    $post = Post::factory()->create(['views' => 7]);
    $post->load('user');
    $post->comments_count = 0;

    $resource = (new PostResource($post))->toArray(new Request());

    expect($resource)->toHaveKeys([
        'id', 'title', 'slug', 'body', 'excerpt', 'featured_image',
        'is_published', 'is_editors_pick', 'published_at', 'reading_time',
        'views', 'comments_count', 'user', 'created_at', 'updated_at'
    ])
        ->and($resource['views'])->toBe(7)
        ->and($resource['user'])->toHaveKeys(['id', 'name'])
        ->and($resource['created_at'])->toBeInstanceOf(Carbon::class)
        ->and($resource['updated_at'])->toBeInstanceOf(Carbon::class);
});

test('PostPublicResource includes optional relations when present', function () {
    $post = new Post();
    $post->setRawAttributes([
        'id'             => 'post-uuid',
        'title'          => 'Public Post',
        'slug'           => 'public-post',
        'body'           => 'Body',
        'excerpt'        => 'Excerpt',
        'featured_image' => 'https://example.com/image.jpg',
        'reading_time'   => 4,
        'views'          => '99',
        'published_at'   => '2026-01-01 00:00:00',
        'is_editors_pick' => true,
        'created_at'     => '2026-01-01 00:00:00',
        'updated_at'     => '2026-01-01 00:00:00',
    ]);

    $post->comments_count = 3;
    $post->is_saved = true;
    $post->category_detail = ['id' => 'cat-1', 'name' => 'Cat'];
    $post->tags_detail = [['id' => 'tag-1', 'name' => 'Tag']];
    $post->author = ['id' => 'author-1', 'name' => 'Author'];

    $resource = (new PostPublicResource($post))->resolve();

    expect($resource)->toHaveKeys([
        'id', 'title', 'slug', 'body', 'excerpt', 'featured_image',
        'reading_time', 'views', 'comments_count', 'is_saved', 'published_at',
        'is_editors_pick', 'category', 'tags', 'author', 'created_at', 'updated_at'
    ])
        ->and($resource['views'])->toBe('99')
        ->and($resource['category'])->toMatchArray(['id' => 'cat-1'])
        ->and($resource['tags'])->toHaveCount(1)
        ->and($resource['author'])->toMatchArray(['id' => 'author-1']);
});

test('PostPublicResource omits optional relations when absent', function () {
    $post = new Post();
    $post->setRawAttributes([
        'id'             => 'post-uuid',
        'title'          => 'Plain Post',
        'slug'           => 'plain-post',
        'body'           => 'Body',
        'excerpt'        => null,
        'featured_image' => null,
        'reading_time'   => 2,
        'views'          => '0',
        'published_at'   => '2026-01-01 00:00:00',
        'is_editors_pick' => false,
        'created_at'     => '2026-01-01 00:00:00',
        'updated_at'     => '2026-01-01 00:00:00',
    ]);

    $resource = (new PostPublicResource($post))->resolve();

    expect($resource)->not->toHaveKeys(['category', 'tags', 'author'])
        ->and($resource)->toHaveKeys(['id', 'title', 'slug']);
});