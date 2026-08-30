<?php

use Modules\Engagement\Services\CommentPublicService;
use Modules\Engagement\Models\Comment;
use Modules\Articles\Models\Post;
use Modules\Profile\Services\Contracts\FetchesPublicProfiles;

beforeEach(function () {
    $this->profileFetcher = Mockery::mock(FetchesPublicProfiles::class);
    $this->profileFetcher->shouldReceive('getPublicProfilesMap')
        ->andReturnUsing(fn ($ids) => array_fill_keys($ids, ['avatar' => null]));
    $this->publicService = new CommentPublicService($this->profileFetcher);
    $this->post = Post::factory()->create();
});

test('getCommentsForPost returns cursor paginator with top-level approved comments and preloaded replies', function () {
    // Create a comment with replies; make it the newest so it appears first.
    $commentWithReplies = Comment::factory()->approved()->create([
        'post_id' => $this->post->id,
        'parent_id' => null,
        'created_at' => now()->addHour(),
    ]);

    // Create 3 replies for the commentWithReplies.
    Comment::factory()->count(3)->approved()->replyTo($commentWithReplies)->create();

    // Create 9 older top-level comments.
    Comment::factory()->count(9)->approved()->create([
        'post_id' => $this->post->id,
        'parent_id' => null,
        'created_at' => now()->subHour(),
    ]);

    $paginator = $this->publicService->getCommentsForPost($this->post->id, null);

    expect($paginator)->toBeInstanceOf(\Illuminate\Pagination\CursorPaginator::class);
    expect($paginator->count())->toBe(10);

    // The first item should be commentWithReplies (newest).
    $firstComment = $paginator->items()[0];
    expect($firstComment->replies)->toHaveCount(2); // preloaded limit
    expect($firstComment->replies_has_more)->toBeTrue();
});

test('getTotalCommentsCount returns total approved comments for post', function () {
    Comment::factory()->count(5)->approved()->create(['post_id' => $this->post->id]);
    Comment::factory()->count(3)->create(['post_id' => $this->post->id, 'is_approved' => false]);

    expect($this->publicService->getTotalCommentsCount($this->post->id))->toBe(5);
});

test('getRepliesForComment returns paginated replies with has_more flag', function () {
    $parent = Comment::factory()->approved()->create(['post_id' => $this->post->id]);
    Comment::factory()->count(12)->approved()->replyTo($parent)->create();

    $result = $this->publicService->getRepliesForComment($parent->id, skip: 0, take: 10);

    expect($result['data'])->toHaveCount(10);
    expect($result['meta']['has_more'])->toBeTrue();

    $result2 = $this->publicService->getRepliesForComment($parent->id, skip: 10, take: 10);
    expect($result2['data'])->toHaveCount(2);
    expect($result2['meta']['has_more'])->toBeFalse();
});