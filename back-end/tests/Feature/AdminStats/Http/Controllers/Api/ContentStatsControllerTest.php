<?php

use Modules\AdminStats\Services\Contracts\ContentStatsContract;
use Shared\Models\User;

beforeEach(function () {
    $this->mockContentStatsService = Mockery::mock(ContentStatsContract::class);
    $this->app->instance(ContentStatsContract::class, $this->mockContentStatsService);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');
    $this->author = User::factory()->create();
    $this->author->assignRole('author');
});

test('dashboard returns stats for admin', function () {
    $stats = [
        'total_posts' => 10,
        'published_posts' => 7,
        'total_views' => 1000,
        'popular_categories' => [],
        'popular_tags' => [],
    ];

    $this->mockContentStatsService
        ->shouldReceive('getDashboardStats')
        ->once()
        ->andReturn($stats);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/admin/stats/dashboard');

    $response->assertOk()
        ->assertJson([
            'data' => $stats,
        ]);
});

test('dashboard returns stats for editor', function () {
    $this->mockContentStatsService
        ->shouldReceive('getDashboardStats')
        ->once()
        ->andReturn(['total_posts' => 0, 'published_posts' => 0, 'total_views' => 0, 'popular_categories' => [], 'popular_tags' => []]);

    $this->actingAs($this->editor, 'sanctum')
        ->getJson('/api/admin/stats/dashboard')
        ->assertOk();
});

test('dashboard denies author', function () {
    $this->mockContentStatsService->shouldNotReceive('getDashboardStats');

    $this->actingAs($this->author, 'sanctum')
        ->getJson('/api/admin/stats/dashboard')
        ->assertStatus(403);
});

test('authors returns stats for admin', function () {
    $authorStats = [
        ['user_id' => 'uuid-1', 'posts_count' => 5, 'total_views' => 100],
        ['user_id' => 'uuid-2', 'posts_count' => 3, 'total_views' => 50],
    ];

    $this->mockContentStatsService
        ->shouldReceive('getAuthorStats')
        ->once()
        ->andReturn($authorStats);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/admin/stats/authors');

    $response->assertOk()
        ->assertJson([
            'data' => $authorStats,
        ]);
});

test('authors returns stats for editor', function () {
    $this->mockContentStatsService
        ->shouldReceive('getAuthorStats')
        ->once()
        ->andReturn([]);

    $this->actingAs($this->editor, 'sanctum')
        ->getJson('/api/admin/stats/authors')
        ->assertOk();
});

test('authors denies author', function () {
    $this->mockContentStatsService->shouldNotReceive('getAuthorStats');

    $this->actingAs($this->author, 'sanctum')
        ->getJson('/api/admin/stats/authors')
        ->assertStatus(403);
});