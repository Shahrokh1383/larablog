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

test('me returns author dashboard stats for admin', function () {
    $stats = ['posts_count' => 5, 'total_views' => 120];
    $userId = $this->admin->id;

    $this->mockContentStatsService
        ->shouldReceive('getAuthorDashboardStats')
        ->once()
        ->with($userId)
        ->andReturn($stats);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/admin/stats/me');

    $response->assertOk()
        ->assertJson([
            'data' => $stats,
        ]);
});

test('me returns author dashboard stats for editor', function () {
    $stats = ['posts_count' => 2, 'total_views' => 30];
    $userId = $this->editor->id;

    $this->mockContentStatsService
        ->shouldReceive('getAuthorDashboardStats')
        ->once()
        ->with($userId)
        ->andReturn($stats);

    $this->actingAs($this->editor, 'sanctum')
        ->getJson('/api/admin/stats/me')
        ->assertOk()
        ->assertJson(['data' => $stats]);
});

test('me returns author dashboard stats for author', function () {
    $stats = ['posts_count' => 0, 'total_views' => 0];
    $userId = $this->author->id;

    $this->mockContentStatsService
        ->shouldReceive('getAuthorDashboardStats')
        ->once()
        ->with($userId)
        ->andReturn($stats);

    $this->actingAs($this->author, 'sanctum')
        ->getJson('/api/admin/stats/me')
        ->assertOk()
        ->assertJson(['data' => $stats]);
});

test('me denies regular user', function () {
    $regularUser = User::factory()->create();
    $regularUser->assignRole('user');

    $this->mockContentStatsService->shouldNotReceive('getAuthorDashboardStats');

    $this->actingAs($regularUser, 'sanctum')
        ->getJson('/api/admin/stats/me')
        ->assertStatus(403);
});

test('commenters returns top commenters for admin', function () {
    $commenters = [
        ['user_id' => 'u1', 'name' => 'Alice', 'email' => 'alice@example.com', 'comments_count' => 3],
    ];

    $this->mockContentStatsService
        ->shouldReceive('getTopCommenters')
        ->once()
        ->andReturn($commenters);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/admin/stats/commenters');

    $response->assertOk()
        ->assertJson([
            'data' => $commenters,
        ]);
});

test('commenters returns top commenters for editor', function () {
    $commenters = [];

    $this->mockContentStatsService
        ->shouldReceive('getTopCommenters')
        ->once()
        ->andReturn($commenters);

    $this->actingAs($this->editor, 'sanctum')
        ->getJson('/api/admin/stats/commenters')
        ->assertOk()
        ->assertJson(['data' => $commenters]);
});

test('commenters denies author', function () {
    $this->mockContentStatsService->shouldNotReceive('getTopCommenters');

    $this->actingAs($this->author, 'sanctum')
        ->getJson('/api/admin/stats/commenters')
        ->assertStatus(403);
});