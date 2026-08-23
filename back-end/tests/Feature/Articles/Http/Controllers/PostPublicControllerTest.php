<?php

use Modules\Articles\Services\Contracts\PostPublicServiceInterface;
use Mockery\MockInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

it('returns paginated posts', function () {
    $paginator = new LengthAwarePaginator([], 0, 10);
    $this->mock(PostPublicServiceInterface::class, function (MockInterface $mock) use ($paginator) {
        $mock->shouldReceive('getPaginatedPosts')
            ->once()
            ->with(10)
            ->andReturn($paginator);
    });

    $response = $this->getJson('/api/posts');

    $response->assertOk()
        ->assertJsonStructure(['data', 'links', 'meta']);
});

it('shows a post by slug', function () {
    $post = new stdClass();
    $post->id = 'uuid';
    $post->title = 'Public Post';
    $this->mock(PostPublicServiceInterface::class, function (MockInterface $mock) use ($post) {
        $mock->shouldReceive('getBySlug')
            ->once()
            ->with('my-post')
            ->andReturn($post);
    });

    $response = $this->getJson('/api/posts/my-post');

    $response->assertOk();
});

it('returns 404 for missing post slug', function () {
    $this->mock(PostPublicServiceInterface::class, function (MockInterface $mock) {
        $mock->shouldReceive('getBySlug')
            ->once()
            ->andReturnNull();
    });

    $response = $this->getJson('/api/posts/missing');

    $response->assertStatus(404);
});

it('returns related posts', function () {
    $this->mock(PostPublicServiceInterface::class, function (MockInterface $mock) {
        $mock->shouldReceive('getRelatedPosts')
            ->once()
            ->with('my-post', 3)
            ->andReturn([]);
    });

    $response = $this->getJson('/api/posts/my-post/related');

    $response->assertOk();
});

it('returns posts by category slug', function () {
    $meta = ['id' => 'cat', 'name' => 'Cat', 'slug' => 'my-category'];
    $paginator = new LengthAwarePaginator([], 0, 10);
    $this->mock(PostPublicServiceInterface::class, function (MockInterface $mock) use ($meta, $paginator) {
        $mock->shouldReceive('getPublishedPostsByCategoryForPublic')
            ->once()
            ->with('my-category', 'newest', 10)
            ->andReturn(['category' => $meta, 'posts' => $paginator]);
    });

    $response = $this->getJson('/api/categories/my-category/posts');

    $response->assertOk()
        ->assertJsonStructure(['category', 'posts']);
});

it('validates category posts query', function () {
    $this->mock(PostPublicServiceInterface::class);

    $response = $this->getJson('/api/categories/my-category/posts?sort=invalid');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['sort']);
});

it('returns posts by tag slug', function () {
    $meta = ['id' => 'tag', 'name' => 'Tag', 'slug' => 'my-tag'];
    $paginator = new LengthAwarePaginator([], 0, 10);
    $this->mock(PostPublicServiceInterface::class, function (MockInterface $mock) use ($meta, $paginator) {
        $mock->shouldReceive('getPublishedPostsByTagForPublic')
            ->once()
            ->with('my-tag', 'newest', 10)
            ->andReturn(['tag' => $meta, 'posts' => $paginator]);
    });

    $response = $this->getJson('/api/tags/my-tag/posts');

    $response->assertOk()
        ->assertJsonStructure(['tag', 'posts']);
});

it('validates tag posts query', function () {
    $this->mock(PostPublicServiceInterface::class);

    $response = $this->getJson('/api/tags/my-tag/posts?per_page=invalid');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['per_page']);
});