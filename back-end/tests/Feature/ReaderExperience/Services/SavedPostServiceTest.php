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
    $post1 = Post::factory()->create();
    $post2 = Post::factory()->create();
    
    SavedPost::create([
        'user_id' => $this->user->id,
        'post_id' => $post1->id,
        'saved_at' => now(),
    ]);
    
    SavedPost::create([
        'user_id' => $this->user->id,
        'post_id' => $post2->id,
        'saved_at' => now()->subMinute(),
    ]);

    $this->postInfoService->shouldReceive('getPostsByIds')
        ->once()
        ->with([$post1->id, $post2->id])
        ->andReturn([
            $post1->id => (object)[
                'id' => $post1->id,
                'title' => 'Saved Post 1',
                'slug' => 'saved-post-1',
                'featured_image' => null,
                'reading_time' => 8,
            ],
            $post2->id => (object)[
                'id' => $post2->id,
                'title' => 'Saved Post 2',
                'slug' => 'saved-post-2',
                'featured_image' => null,
                'reading_time' => 5,
            ],
        ]);

    $paginator = $this->service->listPaginated($this->user->id, 10);

    expect($paginator->total())->toBe(2);
    expect($paginator->items()[0]->post_info->title)->toBe('Saved Post 1');
});

test('listPaginated short-circuits if no saved posts', function () {
    $this->postInfoService->shouldReceive('getPostsByIds')
        ->once()
        ->with([])
        ->andReturn([]);

    $paginator = $this->service->listPaginated($this->user->id);

    expect($paginator->total())->toBe(0);
});

test('getSavedPostIdsForUser returns array of saved post ids from given list', function () {
    $post1 = Post::factory()->create();
    $post2 = Post::factory()->create();
    $post3 = Post::factory()->create();
    
    SavedPost::create(['user_id' => $this->user->id, 'post_id' => $post1->id, 'saved_at' => now()]);
    SavedPost::create(['user_id' => $this->user->id, 'post_id' => $post2->id, 'saved_at' => now()]);

    $ids = [$post1->id, $post2->id, $post3->id];
    $result = $this->service->getSavedPostIdsForUser($this->user->id, $ids);

    expect($result)->toEqualCanonicalizing([$post1->id, $post2->id]);
});

test('getSavedPostIdsForUser returns empty array for empty input', function () {
    $result = $this->service->getSavedPostIdsForUser($this->user->id, []);
    expect($result)->toBe([]);
});