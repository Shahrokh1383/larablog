<?php

use Modules\Taxonomy\Models\Tag;
use Modules\Articles\Services\Contracts\PostPublicServiceInterface;
use Mockery\MockInterface;

it('lists public tags with post counts', function () {
    $tag = Tag::factory()->create();

    $this->mock(PostPublicServiceInterface::class, function (MockInterface $mock) use ($tag) {
        $mock->shouldReceive('getPublishedPostCountsByTags')
            ->once()
            ->andReturn([$tag->id => 5]);
    });

    $response = $this->getJson('/api/tags');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'slug', 'posts_count']
            ],
            'links',
            'meta',
        ]);
});

it('returns popular tags', function () {
    $tag = Tag::factory()->create();
    $stats = [
        ['tag_id' => $tag->id, 'posts_count' => 8, 'total_views' => 80],
    ];

    $this->mock(PostPublicServiceInterface::class, function (MockInterface $mock) use ($stats) {
        $mock->shouldReceive('getPopularTagStats')
            ->once()
            ->with(10)
            ->andReturn($stats);
    });

    $response = $this->getJson('/api/tags/popular');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'slug', 'posts_count', 'total_views']
            ],
        ]);
});

it('returns posts for a tag', function () {
    $tag = Tag::factory()->create();
    $postsArray = [
        'data' => [],
        'links' => [],
        'meta' => [],
    ];

    $this->mock(PostPublicServiceInterface::class, function (MockInterface $mock) use ($tag, $postsArray) {
        $mock->shouldReceive('getPublishedPostCountsByTags')
            ->once()
            ->with([$tag->id])
            ->andReturn([$tag->id => 0]);
        $mock->shouldReceive('getPublishedPostsByTagForPublic')
            ->once()
            ->with($tag->slug, 'newest', 10)
            ->andReturn($postsArray);
    });

    $response = $this->getJson("/api/tags/{$tag->slug}/posts");

    $response->assertOk()
        ->assertJsonStructure([
            'tag' => ['id', 'name', 'slug'],
            'posts' => ['data', 'links', 'meta'],
        ]);
});

it('returns 404 for missing tag posts', function () {
    $this->mock(PostPublicServiceInterface::class);

    $response = $this->getJson('/api/tags/missing/posts');

    $response->assertStatus(404);
});