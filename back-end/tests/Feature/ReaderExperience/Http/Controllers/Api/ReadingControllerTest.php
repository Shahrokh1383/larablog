<?php

use Modules\ReaderExperience\Actions\TrackPostReadAction;
use Modules\ReaderExperience\Models\PostRead;
use Modules\Articles\Models\Post;
use Shared\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'sanctum');
    $this->post = Post::factory()->create();
    Cache::flush();
});

test('store tracks post read and invalidates cache', function () {
    Cache::put("dashboard_overview_{$this->user->id}", 'cached', 60);

    $response = $this->postJson("/api/reading/posts/{$this->post->id}/read");

    $response->assertStatus(204);
    $this->assertDatabaseHas('reader_post_reads', [
        'user_id' => $this->user->id,
        'post_id' => $this->post->id,
    ]);
    expect(Cache::get("dashboard_overview_{$this->user->id}"))->toBeNull();
});