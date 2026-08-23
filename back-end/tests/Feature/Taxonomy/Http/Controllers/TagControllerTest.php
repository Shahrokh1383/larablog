<?php

use Modules\Identity\Models\User;
use Modules\Taxonomy\Models\Tag;
use Laravel\Sanctum\Sanctum;
use Modules\Articles\Services\Contracts\PostAdminStatsServiceInterface;
use Mockery\MockInterface;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->assignRole('admin');
    Sanctum::actingAs($this->user, ['*']);
});

it('lists tags', function () {
    $tag = Tag::factory()->create();

    $this->mock(PostAdminStatsServiceInterface::class, function (MockInterface $mock) use ($tag) {
        $mock->shouldReceive('getTotalPostCountsByTags')
            ->once()
            ->withArgs(fn ($ids) => $ids === [$tag->id])
            ->andReturn([$tag->id => 4]);
    });

    $response = $this->getJson('/api/admin/tags');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'slug', 'posts_count', 'created_at', 'updated_at']
            ],
            'links',
            'meta',
        ]);
});

it('stores a tag', function () {
    $this->mock(PostAdminStatsServiceInterface::class);

    $response = $this->postJson('/api/admin/tags', ['name' => 'New Tag']);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'New Tag')
        ->assertJsonPath('data.slug', 'new-tag');
});

it('validates store request', function () {
    $this->mock(PostAdminStatsServiceInterface::class);

    $response = $this->postJson('/api/admin/tags', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('shows a tag with stats', function () {
    $tag = Tag::factory()->create();

    $this->mock(PostAdminStatsServiceInterface::class, function (MockInterface $mock) use ($tag) {
        $mock->shouldReceive('getTotalPostCountsByTags')
            ->once()
            ->withArgs(fn ($ids) => $ids === [$tag->id])
            ->andReturn([$tag->id => 6]);
    });

    $response = $this->getJson("/api/admin/tags/{$tag->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $tag->id)
        ->assertJsonPath('data.posts_count', 6);
});

it('updates a tag', function () {
    $tag = Tag::factory()->create(['name' => 'Old', 'slug' => 'old']);

    $this->mock(PostAdminStatsServiceInterface::class, function (MockInterface $mock) use ($tag) {
        $mock->shouldReceive('getTotalPostCountsByTags')
            ->once()
            ->withArgs(fn ($ids) => $ids === [$tag->id])
            ->andReturn([$tag->id => 0]);
    });

    $response = $this->putJson("/api/admin/tags/{$tag->id}", ['name' => 'Updated']);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Updated')
        ->assertJsonPath('data.slug', 'updated');
});

it('deletes a tag', function () {
    $tag = Tag::factory()->create();

    $this->mock(PostAdminStatsServiceInterface::class);

    $response = $this->deleteJson("/api/admin/tags/{$tag->id}");

    $response->assertStatus(204);
});

it('forbids author from storing tag', function () {
    $author = User::factory()->create();
    $author->assignRole('author');
    Sanctum::actingAs($author, ['*']);

    $this->mock(PostAdminStatsServiceInterface::class);

    $response = $this->postJson('/api/admin/tags', ['name' => 'Forbidden']);

    $response->assertStatus(403);
});

it('validates index request', function () {
    $this->mock(PostAdminStatsServiceInterface::class);

    $response = $this->getJson('/api/admin/tags?per_page=invalid');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['per_page']);
});

it('returns 404 for missing tag show', function () {
    $this->mock(PostAdminStatsServiceInterface::class);

    $response = $this->getJson('/api/admin/tags/00000000-0000-0000-0000-000000000000');

    $response->assertStatus(404);
});

it('allows editor to store tag', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');
    Sanctum::actingAs($editor, ['*']);

    $this->mock(PostAdminStatsServiceInterface::class);

    $response = $this->postJson('/api/admin/tags', ['name' => 'Editor Tag']);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Editor Tag');
});

it('forbids author from updating tag', function () {
    $author = User::factory()->create();
    $author->assignRole('author');
    Sanctum::actingAs($author, ['*']);

    $tag = Tag::factory()->create();

    $this->mock(PostAdminStatsServiceInterface::class);

    $response = $this->putJson("/api/admin/tags/{$tag->id}", ['name' => 'Updated']);

    $response->assertStatus(403);
});

it('allows author to view tags', function () {
    $author = User::factory()->create();
    $author->assignRole('author');
    Sanctum::actingAs($author, ['*']);

    $tag = Tag::factory()->create();

    $this->mock(PostAdminStatsServiceInterface::class, function (MockInterface $mock) use ($tag) {
        $mock->shouldReceive('getTotalPostCountsByTags')
            ->once()
            ->withArgs(fn ($ids) => $ids === [$tag->id])
            ->andReturn([$tag->id => 0]);
    });

    $response = $this->getJson('/api/admin/tags');

    $response->assertOk();
});