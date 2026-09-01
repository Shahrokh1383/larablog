<?php

use Modules\ReaderExperience\Services\SavedPostService;
use Modules\ReaderExperience\Models\SavedPost;
use Modules\Articles\Models\Post;
use Shared\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'sanctum');
    $this->post = Post::factory()->create();
});

test('toggle saves post and returns saved true', function () {
    $response = $this->postJson("/api/saved-posts/{$this->post->id}");

    $response->assertOk()
        ->assertJson(['saved' => true]);
    $this->assertDatabaseHas('reader_saved_posts', [
        'user_id' => $this->user->id,
        'post_id' => $this->post->id,
    ]);
});

test('toggle unsaves post and returns saved false', function () {
    SavedPost::factory()->create([
        'user_id' => $this->user->id,
        'post_id' => $this->post->id,
    ]);

    $response = $this->postJson("/api/saved-posts/{$this->post->id}");

    $response->assertOk()
        ->assertJson(['saved' => false]);
    $this->assertDatabaseMissing('reader_saved_posts', [
        'user_id' => $this->user->id,
        'post_id' => $this->post->id,
    ]);
});

test('index returns paginated saved posts with post info', function () {
    SavedPost::factory()->count(2)->create([
        'user_id' => $this->user->id,
        'post_id' => $this->post->id,
    ]);

    // Mock PostInfoContract in container
    $postInfoService = Mockery::mock(\Modules\Articles\Services\Contracts\PostInfoContract::class);
    $postInfoService->shouldReceive('getPostsByIds')
        ->once()
        ->andReturn([
            $this->post->id => (object)[
                'id' => $this->post->id,
                'title' => 'Saved Post',
                'slug' => 'saved-post',
                'featured_image' => null,
                'reading_time' => 5,
            ]
        ]);
    $this->app->instance(\Modules\Articles\Services\Contracts\PostInfoContract::class, $postInfoService);

    $response = $this->getJson('/api/saved-posts?per_page=10');

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.post.title', 'Saved Post')
        ->assertJsonPath('meta.total', 2);
});