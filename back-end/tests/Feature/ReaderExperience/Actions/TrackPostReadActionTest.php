<?php

use Modules\ReaderExperience\Actions\TrackPostReadAction;
use Modules\ReaderExperience\Models\PostRead;
use Modules\Articles\Models\Post;
use Shared\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->action = new TrackPostReadAction();
    $this->user = User::factory()->create();
    $this->post = Post::factory()->create();
    Cache::flush();
});

test('execute creates new post read if none exists', function () {
    $this->action->execute($this->user->id, $this->post->id);

    $this->assertDatabaseHas('reader_post_reads', [
        'user_id' => $this->user->id,
        'post_id' => $this->post->id,
    ]);
});

test('execute updates existing post read timestamp', function () {
    $existing = PostRead::create([
        'user_id' => $this->user->id,
        'post_id' => $this->post->id,
        'read_at' => now()->subDay(),
    ]);

    $this->action->execute($this->user->id, $this->post->id);

    $this->assertTrue($existing->fresh()->read_at->gt(now()->subMinute()));
    $this->assertSame(1, PostRead::where('user_id', $this->user->id)->count());
});

test('execute invalidates dashboard cache for user', function () {
    Cache::put("dashboard_overview_{$this->user->id}", ['dummy'], 60);

    $this->action->execute($this->user->id, $this->post->id);

    expect(Cache::get("dashboard_overview_{$this->user->id}"))->toBeNull();
});