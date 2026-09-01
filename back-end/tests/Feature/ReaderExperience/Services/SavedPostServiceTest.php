<?php

use Modules\ReaderExperience\Services\SavedPostService;
use Modules\ReaderExperience\Actions\ToggleSavedPostAction;
use Modules\ReaderExperience\Models\SavedPost;
use Modules\Articles\Services\Contracts\PostInfoContract;
use Modules\Articles\Models\Post;
use Shared\Models\User;

beforeEach(function () {
    $this->toggleAction = Mockery::mock(ToggleSavedPostAction::class);
    $this->postInfoService = Mockery::mock(PostInfoContract::class);
    $this->service = new SavedPostService($this->toggleAction, $this->postInfoService);
    $this->user = User::factory()->create();
});

test('toggle delegates to action and returns result', function () {
    $postId = 'post-uuid';
    $this->toggleAction->shouldReceive('execute')
        ->once()
        ->with($this->user->id, $postId)
        ->andReturn(true);

    $result = $this->service->toggle($this->user->id, $postId);

    expect($result)->toBeTrue();
});

test('listPaginated returns saved posts enriched with post info', function () {
    $post = Post::factory()->create();
    SavedPost::factory()->count(2)->create([
        'user_id' => $this->user->id,
        'post_id' => $post->id,
        'saved_at' => now(),
    ]);

    $this->postInfoService->shouldReceive('getPostsByIds')
        ->once()
        ->with([$post->id])
        ->andReturn([
            $post->id => (object)[
                'id' => $post->id,
                'title' => 'Saved Post',
                'slug' => 'saved-post',
                'featured_image' => null,
                'reading_time' => 8,
            ],
        ]);

    $paginator = $this->service->listPaginated($this->user->id, 10);

    expect($paginator->total())->toBe(2);
    expect($paginator->items()[0]->post_info->title)->toBe('Saved Post');
});

test('listPaginated short-circuits if no saved posts', function () {
    $this->postInfoService->shouldNotReceive('getPostsByIds');

    $paginator = $this->service->listPaginated($this->user->id);

    expect($paginator->total())->toBe(0);
});

test('getSavedPostIdsForUser returns array of saved post ids from given list', function () {
    $post1 = Post::factory()->create();
    $post2 = Post::factory()->create();
    $post3 = Post::factory()->create();
    SavedPost::factory()->create(['user_id' => $this->user->id, 'post_id' => $post1->id]);
    SavedPost::factory()->create(['user_id' => $this->user->id, 'post_id' => $post2->id]);

    $ids = [$post1->id, $post2->id, $post3->id];
    $result = $this->service->getSavedPostIdsForUser($this->user->id, $ids);

    expect($result)->toMatchArray([$post1->id, $post2->id]);
});

test('getSavedPostIdsForUser returns empty array for empty input', function () {
    $result = $this->service->getSavedPostIdsForUser($this->user->id, []);
    expect($result)->toBe([]);
});