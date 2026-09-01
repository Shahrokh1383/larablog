<?php

use Modules\ReaderExperience\Actions\ToggleSavedPostAction;
use Modules\ReaderExperience\Models\SavedPost;
use Modules\Articles\Models\Post;
use Shared\Models\User;

beforeEach(function () {
    $this->action = new ToggleSavedPostAction();
    $this->user = User::factory()->create();
    $this->post = Post::factory()->create();
});

test('execute saves post when not saved', function () {
    $result = $this->action->execute($this->user->id, $this->post->id);

    expect($result)->toBeTrue();
    $this->assertDatabaseHas('reader_saved_posts', [
        'user_id' => $this->user->id,
        'post_id' => $this->post->id,
    ]);
});

test('execute unsaves post when already saved', function () {
    SavedPost::create([
        'user_id' => $this->user->id,
        'post_id' => $this->post->id,
        'saved_at' => now(),
    ]);

    $result = $this->action->execute($this->user->id, $this->post->id);

    expect($result)->toBeFalse();
    $this->assertDatabaseMissing('reader_saved_posts', [
        'user_id' => $this->user->id,
        'post_id' => $this->post->id,
    ]);
});