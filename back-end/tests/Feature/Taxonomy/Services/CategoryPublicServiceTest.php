<?php

use Modules\Taxonomy\Models\Category;
use Modules\Taxonomy\Services\CategoryPublicService;
use Modules\Articles\Services\Contracts\PostStatsServiceInterface;
use Mockery\MockInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

it('returns public categories with aggregate counts', function () {
    $cat1 = Category::factory()->create();
    $cat2 = Category::factory()->create();

    $this->mock(PostStatsServiceInterface::class, function (MockInterface $mock) use ($cat1, $cat2) {
        $mock->shouldReceive('getPublishedPostCountsByCategories')
            ->once()
            ->withArgs(fn ($ids) => count($ids) === 2)
            ->andReturn([
                $cat1->id => 5,
                $cat2->id => 10,
            ]);
        $mock->shouldReceive('getDistinctAuthorCountsByCategories')
            ->once()
            ->withArgs(fn ($ids) => count($ids) === 2)
            ->andReturn([
                $cat1->id => 2,
                $cat2->id => 3,
            ]);
    });

    $service = app(CategoryPublicService::class);
    $paginator = $service->getPublicCategories(null, 10);

    expect($paginator->total())->toBe(2);
    $items = $paginator->items();
    expect($items[0]->posts_count)->toBeIn([5, 10])
        ->and($items[0]->authors_count)->toBeIn([2, 3]);
});

it('returns public category meta by slug with aggregates', function () {
    $category = Category::factory()->create(['slug' => 'known-slug']);

    $this->mock(PostStatsServiceInterface::class, function (MockInterface $mock) use ($category) {
        $mock->shouldReceive('getPublishedPostCountsByCategories')
            ->once()
            ->with([$category->id])
            ->andReturn([$category->id => 4]);
        $mock->shouldReceive('getDistinctAuthorCountsByCategories')
            ->once()
            ->with([$category->id])
            ->andReturn([$category->id => 1]);
    });

    $service = app(CategoryPublicService::class);
    $result = $service->getCategoryMetaBySlug('known-slug');

    expect($result['id'])->toBe((string) $category->id)
        ->and($result['posts_count'])->toBe(4)
        ->and($result['authors_count'])->toBe(1);
});

it('throws ModelNotFoundException for missing category slug', function () {
    $this->mock(PostStatsServiceInterface::class);

    $service = app(CategoryPublicService::class);

    expect(fn () => $service->getCategoryMetaBySlug('missing'))->toThrow(ModelNotFoundException::class);
});

it('returns popular categories preserving order', function () {
    $cat1 = Category::factory()->create();
    $cat2 = Category::factory()->create();

    $stats = [
        ['category_id' => $cat2->id, 'posts_count' => 20],
        ['category_id' => $cat1->id, 'posts_count' => 10],
    ];

    $this->mock(PostStatsServiceInterface::class, function (MockInterface $mock) use ($stats) {
        $mock->shouldReceive('getPopularCategoryStats')
            ->once()
            ->with(2)
            ->andReturn($stats);
    });

    $service = app(CategoryPublicService::class);
    $result = $service->getPopularCategories(2);

    expect($result)->toHaveCount(2)
        ->and($result[0]['id'])->toBe($cat2->id)
        ->and($result[0]['posts_count'])->toBe(20)
        ->and($result[1]['id'])->toBe($cat1->id);
});