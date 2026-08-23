<?php

use Modules\Identity\Models\User;
use Modules\Articles\Models\Post;
use Modules\Articles\Services\Contracts\PostAdminServiceInterface;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->assignRole('admin');
    Sanctum::actingAs($this->user, ['*']);
});

it('lists posts', function () {
    $post = Post::factory()->create();

    $this->mock(PostAdminServiceInterface::class, function (MockInterface $mock) {
        $mock->shouldReceive('getAll')
            ->once()
            ->andReturn(new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15));
    });

    $response = $this->getJson('/api/admin/posts');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [],
            'links',
            'meta',
        ]);
});

it('stores a post', function () {
    $post = Post::factory()->make(['title' => 'New Post']);
    $this->mock(PostAdminServiceInterface::class, function (MockInterface $mock) use ($post) {
        $mock->shouldReceive('create')
            ->once()
            ->andReturn($post);
    });

    $response = $this->postJson('/api/admin/posts', [
        'title' => 'New Post',
        'body' => 'Post body',
    ]);

    $response->assertCreated();
});

it('validates store request', function () {
    $response = $this->postJson('/api/admin/posts', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'body']);
});

it('shows a post', function () {
    $post = Post::factory()->create();
    $this->mock(PostAdminServiceInterface::class, function (MockInterface $mock) use ($post) {
        $mock->shouldReceive('enrich')
            ->once()
            ->andReturn($post);
    });

    $response = $this->getJson("/api/admin/posts/{$post->id}");

    $response->assertOk();
});

it('updates a post', function () {
    $post = Post::factory()->create(['title' => 'Old']);
    $this->mock(PostAdminServiceInterface::class, function (MockInterface $mock) use ($post) {
        $mock->shouldReceive('update')
            ->once()
            ->andReturn($post);
    });

    $response = $this->putJson("/api/admin/posts/{$post->id}", ['title' => 'Updated']);

    $response->assertOk();
});

it('deletes a post', function () {
    $post = Post::factory()->create();
    $this->mock(PostAdminServiceInterface::class, function (MockInterface $mock) use ($post) {
        $mock->shouldReceive('delete')
            ->once()
            ->with($post);
    });

    $response = $this->deleteJson("/api/admin/posts/{$post->id}");

    $response->assertStatus(204);
});

it('forbids author from deleting post', function () {
    $author = User::factory()->create();
    $author->assignRole('author');
    Sanctum::actingAs($author, ['*']);

    $post = Post::factory()->create(['user_id' => $author->id]);
    $this->mock(PostAdminServiceInterface::class);

    $response = $this->deleteJson("/api/admin/posts/{$post->id}");

    $response->assertStatus(403);
});

it('uploads image', function () {
    \Illuminate\Support\Facades\Storage::fake('public');
    $file = \Illuminate\Http\UploadedFile::fake()->image('post.jpg');

    $this->mock(PostAdminServiceInterface::class, function (MockInterface $mock) {
        $mock->shouldReceive('uploadImage')
            ->once()
            ->andReturn('http://localhost/storage/posts/images/post.jpg');
    });

    $response = $this->postJson('/api/admin/posts/upload-image', ['image' => $file]);

    $response->assertOk()
        ->assertJsonPath('url', 'http://localhost/storage/posts/images/post.jpg');
});

it('deletes image', function () {
    $this->mock(PostAdminServiceInterface::class, function (MockInterface $mock) {
        $mock->shouldReceive('deleteImage')
            ->once()
            ->andReturnTrue();
    });

    $response = $this->deleteJson('/api/admin/posts/delete-image', ['url' => 'http://localhost/storage/posts/images/post.jpg']);

    $response->assertOk()
        ->assertJsonPath('success', true);
});