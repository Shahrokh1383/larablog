<?php

use Modules\Identity\Models\User;
use Modules\Taxonomy\Models\Tag;
use Laravel\Sanctum\Sanctum;
use Modules\Articles\Services\Contracts\PostAdminServiceInterface;
use Mockery\MockInterface;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->assignRole('admin');
    Sanctum::actingAs($this->user, ['*']);
});

it('lists tags', function () {
    $tag = Tag::factory()->create();

    $this->mock(PostAdminServiceInterface::class, function (MockInterface $mock) use ($tag) {
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
    $this->mock(PostAdminServiceInterface::class);

    $response = $this->postJson('/api/admin/tags', ['name' => 'New Tag']);

    $response->assertOk()
        ->assertJsonPath('data.name', 'New Tag')
        ->assertJsonPath('data.slug', 'new-tag');
});

it('validates store request', function () {
    $this->mock(PostAdminServiceInterface::class);

    $response = $this->postJson('/api/admin/tags', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('shows a tag with stats', function () {
    $tag = Tag::factory()->create();

    $this->mock(PostAdminServiceInterface::class, function (MockInterface $mock) use ($tag) {
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

    $this->mock(PostAdminServiceInterface::class);

    $response = $this->putJson("/api/admin/tags/{$tag->id}", ['name' => 'Updated']);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Updated')
        ->assertJsonPath('data.slug', 'updated');
});

it('deletes a tag', function () {
    $tag = Tag::factory()->create();

    $this->mock(PostAdminServiceInterface::class);

    $response = $this->deleteJson("/api/admin/tags/{$tag->id}");

    $response->assertStatus(204);
});

it('forbids author from storing tag', function () {
    $author = User::factory()->create();
    $author->assignRole('author');
    Sanctum::actingAs($author, ['*']);

    $this->mock(PostAdminServiceInterface::class);

    $response = $this->postJson('/api/admin/tags', ['name' => 'Forbidden']);

    $response->assertStatus(403);
});