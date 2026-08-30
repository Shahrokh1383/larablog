<?php

use Modules\Engagement\Services\CommentStatsService;
use Modules\Engagement\Models\Comment;
use Modules\Articles\Models\Post;
use Shared\Models\User;
use Modules\Articles\Services\Contracts\PostInfoContract;
use Carbon\Carbon;

beforeEach(function () {
    $this->postInfoService = Mockery::mock(PostInfoContract::class);
    $this->statsService = new CommentStatsService($this->postInfoService);
    $this->post = Post::factory()->create();
});

test('getCommentCountsForPosts returns approved counts keyed by post_id', function () {
    $post2 = Post::factory()->create();
    Comment::factory()->approved()->count(3)->create(['post_id' => $this->post->id]);
    Comment::factory()->approved()->count(2)->create(['post_id' => $post2->id]);
    Comment::factory()->create(['post_id' => $this->post->id, 'is_approved' => false]);

    $counts = $this->statsService->getCommentCountsForPosts([$this->post->id, $post2->id]);

    $expected = [
        $this->post->id => 3,
        $post2->id => 2,
    ];
    expect($counts)->toEqualCanonicalizing($expected);
});

test('getUserCommentsPaginated returns paginator with post info attached', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    Comment::factory()->approved()->count(5)->byUser($user)->create(['post_id' => $post->id]);

    $this->postInfoService->shouldReceive('getPostsByIds')
        ->once()
        ->with(Mockery::on(function ($ids) use ($post) {
            return in_array($post->id, $ids, true);
        }))
        ->andReturn([
            $post->id => (object)['slug' => 'post-1', 'title' => 'Post 1'],
        ]);

    $paginator = $this->statsService->getUserCommentsPaginated($user->id, 15);
    expect($paginator->total())->toBe(5);
    $first = $paginator->items()[0];
    expect($first->post_slug)->toBe('post-1');
    expect($first->post_title)->toBe('Post 1');
});

test('getWeeklyTopCommenters returns correct ranking', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Comment::factory()->approved()->count(5)->byUser($user1)->create(['created_at' => Carbon::now()->subDays(1)]);
    Comment::factory()->approved()->count(3)->byUser($user2)->create(['created_at' => Carbon::now()->subDays(1)]);
    Comment::factory()->approved()->count(1)->byUser($user1)->create(['created_at' => Carbon::now()->subWeeks(2)]); // old

    $top = $this->statsService->getWeeklyTopCommenters(10);
    expect($top)->toHaveCount(2);
    expect($top->first()->user_id)->toBe($user1->id);
    expect($top->first()->comments_count)->toBe(5);
});

test('getWeeklyCommentCountForUser counts only this week approved comments', function () {
    $user = User::factory()->create();
    Comment::factory()->approved()->count(2)->byUser($user)->create(['created_at' => Carbon::now()->subDays(1)]);
    Comment::factory()->approved()->count(3)->byUser($user)->create(['created_at' => Carbon::now()->subWeeks(2)]);

    expect($this->statsService->getWeeklyCommentCountForUser($user->id))->toBe(2);
});

test('getTotalCommentCountForUser counts all approved comments', function () {
    $user = User::factory()->create();
    Comment::factory()->approved()->count(4)->byUser($user)->create();
    Comment::factory()->byUser($user)->create(['is_approved' => false]);

    expect($this->statsService->getTotalCommentCountForUser($user->id))->toBe(4);
});