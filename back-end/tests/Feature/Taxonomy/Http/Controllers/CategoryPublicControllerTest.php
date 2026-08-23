<?php

use Modules\Taxonomy\Models\Category;
use Modules\Articles\Services\Contracts\PostStatsServiceInterface;
use Modules\Articles\Services\Contracts\PostPublicServiceInterface;
use Mockery\MockInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

it('lists public categories with aggregates', function () {
    $category = Category::factory()->create();

    $this->mock(PostStatsServiceInterface::class, function (MockInterface $mock) use ($category) {
        $mock->shouldReceive('getPublishedPostCountsByCategories')
            ->once()
            ->andReturn([$category->id => 4]);
        $mock->shouldReceive('getDistinctAuthorCountsByCategories')
            ->once()
            ->andReturn([$category->id => 2]);
    });

    $response = $this->getJson('/api/categories');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'slug', 'posts_count', 'authors_count']
            ],
            'links',
            'meta',
        ]);
});

it('returns posts for a category', function () {
    $category = Category::factory()->create();
    
    $categoryMeta = [
        'id' => (string) $category->id,
        'name' => $category->name,
        'slug' => $category->slug,
        'posts_count' => 0,
        'authors_count' => 0,
    ];
    $paginator = new LengthAwarePaginator([], 0, 10);

    $this->mock(PostPublicServiceInterface::class, function (MockInterface $mock) use ($category, $categoryMeta, $paginator) {
        $mock->shouldReceive('getPublishedPostsByCategoryForPublic')
            ->once()
            ->with($category->slug, 'newest', 10)
            ->andReturn([
                'category' => $categoryMeta,
                'posts' => $paginator,
            ]);
    });

    $response = $this->getJson("/api/categories/{$category->slug}/posts");

    $response->assertOk()
        ->assertJsonStructure([
            'category' => ['id', 'name', 'slug'],
            'posts' => ['data', 'links', 'meta'],
        ]);
});

it('returns 404 for missing category posts', function () {
    $this->mock(PostPublicServiceInterface::class, function (MockInterface $mock) {
        $mock->shouldReceive('getPublishedPostsByCategoryForPublic')
            ->once()
            ->andThrow(new ModelNotFoundException());
    });

    $response = $this->getJson('/api/categories/missing/posts');

    $response->assertStatus(404);
});

it('validates public categories index request', function () {
    $this->mock(PostStatsServiceInterface::class);

    $response = $this->getJson('/api/categories?per_page=invalid');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['per_page']);
});