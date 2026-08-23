<?php

use Modules\Taxonomy\Models\Tag;
use Modules\Articles\Services\Contracts\PostStatsServiceInterface;
use Modules\Articles\Services\Contracts\PostPublicServiceInterface;
use Mockery\MockInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

it('lists public tags with post counts', function () {
    $tag = Tag::factory()->create();

    $this->mock(PostStatsServiceInterface::class, function (MockInterface $mock) use ($tag) {
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

    $this->mock(PostStatsServiceInterface::class, function (MockInterface $mock) use ($stats) {
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
    
    $tagMeta = [
        'id' => (string) $tag->id,
        'name' => $tag->name,
        'slug' => $tag->slug,
        'posts_count' => 0,
    ];
    $paginator = new LengthAwarePaginator([], 0, 10);

    $this->mock(PostPublicServiceInterface::class, function (MockInterface $mock) use ($tag, $tagMeta, $paginator) {
        $mock->shouldReceive('getPublishedPostsByTagForPublic')
            ->once()
            ->with($tag->slug, 'newest', 10)
            ->andReturn([
                'tag' => $tagMeta,
                'posts' => $paginator,
            ]);
    });

    $response = $this->getJson("/api/tags/{$tag->slug}/posts");

    $response->assertOk()
        ->assertJsonStructure([
            'tag' => ['id', 'name', 'slug'],
            'posts' => ['data', 'links', 'meta'],
        ]);
});

it('returns 404 for missing tag posts', function () {
    $this->mock(PostPublicServiceInterface::class, function (MockInterface $mock) {
        $mock->shouldReceive('getPublishedPostsByTagForPublic')
            ->once()
            ->andThrow(new ModelNotFoundException());
    });

    $response = $this->getJson('/api/tags/missing/posts');

    $response->assertStatus(404);
});

it('validates public tags index request', function () {
    $this->mock(PostStatsServiceInterface::class);

    $response = $this->getJson('/api/tags?per_page=invalid');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['per_page']);
});

it('returns empty data for popular tags when no stats', function () {
    $this->mock(PostStatsServiceInterface::class, function (MockInterface $mock) {
        $mock->shouldReceive('getPopularTagStats')
            ->once()
            ->with(10)
            ->andReturn([]);
    });

    $response = $this->getJson('/api/tags/popular');

    $response->assertOk()
        ->assertJson(['data' => []]);
});