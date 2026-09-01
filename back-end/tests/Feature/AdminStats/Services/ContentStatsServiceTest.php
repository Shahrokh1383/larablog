<?php

use Modules\AdminStats\Services\ContentStatsService;
use Modules\Articles\Services\Contracts\PostAdminStatsServiceInterface;
use Modules\Taxonomy\Services\Contracts\CategoryAdminServiceInterface;
use Modules\Taxonomy\Services\Contracts\TagAdminServiceInterface;

beforeEach(function () {
    $this->mockPostAdminStats = Mockery::mock(PostAdminStatsServiceInterface::class);
    $this->mockCategoryAdmin = Mockery::mock(CategoryAdminServiceInterface::class);
    $this->mockTagAdmin = Mockery::mock(TagAdminServiceInterface::class);

    $this->service = new ContentStatsService(
        $this->mockPostAdminStats,
        $this->mockCategoryAdmin,
        $this->mockTagAdmin
    );
});

test('getDashboardStats composes stats from post, category, and tag services', function () {
    // Mock post stats
    $this->mockPostAdminStats->shouldReceive('getTotalPostsCount')->once()->andReturn(10);
    $this->mockPostAdminStats->shouldReceive('getPublishedPostsCount')->once()->andReturn(7);
    $this->mockPostAdminStats->shouldReceive('getTotalViews')->once()->andReturn(1500);

    // Popular categories
    $catStats = [
        ['category_id' => 'cat-1', 'posts_count' => 3],
        ['category_id' => 'cat-2', 'posts_count' => 2],
    ];
    $this->mockPostAdminStats->shouldReceive('getPopularCategoryStats')->once()->with(5)->andReturn($catStats);
    $this->mockCategoryAdmin->shouldReceive('getByIds')->once()->with(['cat-1', 'cat-2'])->andReturn([
        'cat-1' => ['id' => 'cat-1', 'name' => 'Laravel', 'slug' => 'laravel'],
        'cat-2' => ['id' => 'cat-2', 'name' => 'PHP', 'slug' => 'php'],
    ]);

    // Popular tags
    $tagStats = [
        ['tag_id' => 'tag-1', 'posts_count' => 4],
    ];
    $this->mockPostAdminStats->shouldReceive('getPopularTagStats')->once()->with(10)->andReturn($tagStats);
    $this->mockTagAdmin->shouldReceive('getByIds')->once()->with(['tag-1'])->andReturn([
        'tag-1' => ['id' => 'tag-1', 'name' => 'Eloquent', 'slug' => 'eloquent'],
    ]);

    $result = $this->service->getDashboardStats();

    expect($result)->toHaveKeys([
        'total_posts', 'published_posts', 'total_views',
        'popular_categories', 'popular_tags',
    ]);
    expect($result['total_posts'])->toBe(10);
    expect($result['published_posts'])->toBe(7);
    expect($result['total_views'])->toBe(1500);
    expect($result['popular_categories'])->toHaveCount(2);
    expect($result['popular_categories'][0])->toMatchArray([
        'id' => 'cat-1',
        'name' => 'Laravel',
        'slug' => 'laravel',
        'posts_count' => 3,
    ]);
    expect($result['popular_tags'])->toHaveCount(1);
    expect($result['popular_tags'][0])->toMatchArray([
        'id' => 'tag-1',
        'name' => 'Eloquent',
        'slug' => 'eloquent',
        'posts_count' => 4,
    ]);
});

test('getDashboardStats handles empty popular stats', function () {
    $this->mockPostAdminStats->shouldReceive('getTotalPostsCount')->once()->andReturn(0);
    $this->mockPostAdminStats->shouldReceive('getPublishedPostsCount')->once()->andReturn(0);
    $this->mockPostAdminStats->shouldReceive('getTotalViews')->once()->andReturn(0);
    $this->mockPostAdminStats->shouldReceive('getPopularCategoryStats')->once()->with(5)->andReturn([]);
    $this->mockPostAdminStats->shouldReceive('getPopularTagStats')->once()->with(10)->andReturn([]);
    $this->mockCategoryAdmin->shouldReceive('getByIds')->once()->with([])->andReturn([]);
    $this->mockTagAdmin->shouldReceive('getByIds')->once()->with([])->andReturn([]);

    $result = $this->service->getDashboardStats();

    expect($result['popular_categories'])->toBe([]);
    expect($result['popular_tags'])->toBe([]);
});

test('getAuthorStats delegates to post admin service', function () {
    $stats = [
        ['user_id' => 'uuid-1', 'posts_count' => 5, 'total_views' => 100],
    ];
    $this->mockPostAdminStats->shouldReceive('getAuthorStats')->once()->andReturn($stats);

    $result = $this->service->getAuthorStats();

    expect($result)->toBe($stats);
});

test('getAuthorStatsForUserIds delegates to post admin service', function () {
    $userIds = ['uuid-1', 'uuid-2'];
    $stats = [
        'uuid-1' => ['posts_count' => 3, 'total_views' => 50],
        'uuid-2' => ['posts_count' => 7, 'total_views' => 200],
    ];
    $this->mockPostAdminStats->shouldReceive('getAuthorStatsForUserIds')->once()->with($userIds)->andReturn($stats);

    $result = $this->service->getAuthorStatsForUserIds($userIds);

    expect($result)->toBe($stats);
});

test('getAuthorStatsForUserId delegates to post admin service', function () {
    $userId = 'uuid-1';
    $stats = ['posts_count' => 3, 'total_views' => 50];
    $this->mockPostAdminStats->shouldReceive('getAuthorStatsForUserId')->once()->with($userId)->andReturn($stats);

    $result = $this->service->getAuthorStatsForUserId($userId);

    expect($result)->toBe($stats);
});