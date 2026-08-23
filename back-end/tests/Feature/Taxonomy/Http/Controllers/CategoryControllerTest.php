<?php

use Modules\Identity\Models\User;
use Modules\Taxonomy\Models\Category;
use Laravel\Sanctum\Sanctum;
use Modules\Articles\Services\Contracts\PostAdminStatsServiceInterface;
use Mockery\MockInterface;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->assignRole('admin');
    Sanctum::actingAs($this->user, ['*']);
});

it('lists categories', function () {
    $category = Category::factory()->create();

    $this->mock(PostAdminStatsServiceInterface::class, function (MockInterface $mock) use ($category) {
        $mock->shouldReceive('getTotalPostCountsByCategories')
            ->once()
            ->withArgs(fn ($ids) => $ids === [$category->id])
            ->andReturn([$category->id => 3]);
    });

    $response = $this->getJson('/api/admin/categories');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'slug', 'posts_count', 'created_at', 'updated_at']
            ],
            'links',
            'meta',
        ]);
});

it('stores a category', function () {
    $this->mock(PostAdminStatsServiceInterface::class);

    $response = $this->postJson('/api/admin/categories', ['name' => 'New Category']);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'New Category')
        ->assertJsonPath('data.slug', 'new-category');
});

it('validates store request', function () {
    $this->mock(PostAdminStatsServiceInterface::class);

    $response = $this->postJson('/api/admin/categories', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('shows a category with stats', function () {
    $category = Category::factory()->create();

    $this->mock(PostAdminStatsServiceInterface::class, function (MockInterface $mock) use ($category) {
        $mock->shouldReceive('getTotalPostCountsByCategories')
            ->once()
            ->withArgs(fn ($ids) => $ids === [$category->id])
            ->andReturn([$category->id => 5]);
    });

    $response = $this->getJson("/api/admin/categories/{$category->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $category->id)
        ->assertJsonPath('data.posts_count', 5);
});

it('updates a category', function () {
    $category = Category::factory()->create(['name' => 'Old', 'slug' => 'old']);

    $this->mock(PostAdminStatsServiceInterface::class, function (MockInterface $mock) use ($category) {
        $mock->shouldReceive('getTotalPostCountsByCategories')
            ->once()
            ->withArgs(fn ($ids) => $ids === [$category->id])
            ->andReturn([$category->id => 0]);
    });

    $response = $this->putJson("/api/admin/categories/{$category->id}", ['name' => 'Updated']);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Updated')
        ->assertJsonPath('data.slug', 'updated');
});

it('deletes a category', function () {
    $category = Category::factory()->create();

    $this->mock(PostAdminStatsServiceInterface::class);

    $response = $this->deleteJson("/api/admin/categories/{$category->id}");

    $response->assertStatus(204);
});

it('forbids author from storing category', function () {
    $author = User::factory()->create();
    $author->assignRole('author');
    Sanctum::actingAs($author, ['*']);

    $this->mock(PostAdminStatsServiceInterface::class);

    $response = $this->postJson('/api/admin/categories', ['name' => 'Forbidden']);

    $response->assertStatus(403);
});