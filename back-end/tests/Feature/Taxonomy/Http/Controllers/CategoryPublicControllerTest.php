<?php

use Modules\Taxonomy\Models\Category;
use Modules\Articles\Services\Contracts\PostPublicServiceInterface;
use Mockery\MockInterface;

it('lists public categories with aggregates', function () {
    $category = Category::factory()->create();

    $this->mock(PostPublicServiceInterface::class, function (MockInterface $mock) use ($category) {
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
    $postsArray = [
        'data' => [],
        'links' => [],
        'meta' => [],
    ];

    $this->mock(PostPublicServiceInterface::class, function (MockInterface $mock) use ($category, $postsArray) {
        $mock->shouldReceive('getPublishedPostCountsByCategories')
            ->once()
            ->with([$category->id])
            ->andReturn([$category->id => 0]);
        $mock->shouldReceive('getDistinctAuthorCountsByCategories')
            ->once()
            ->with([$category->id])
            ->andReturn([$category->id => 0]);
        $mock->shouldReceive('getPublishedPostsByCategoryForPublic')
            ->once()
            ->with($category->slug, 'newest', 10)
            ->andReturn($postsArray);
    });

    $response = $this->getJson("/api/categories/{$category->slug}/posts");

    $response->assertOk()
        ->assertJsonStructure([
            'category' => ['id', 'name', 'slug'],
            'posts' => ['data', 'links', 'meta'],
        ]);
});

it('returns 404 for missing category posts', function () {
    $this->mock(PostPublicServiceInterface::class);

    $response = $this->getJson('/api/categories/missing/posts');

    $response->assertStatus(404);
});