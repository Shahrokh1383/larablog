<?php

use Modules\ReaderExperience\Services\DashboardService;
use Modules\Articles\Services\Contracts\PostInfoContract;
use Modules\Engagement\Services\Contracts\CommentServiceInterface;
use Modules\ReaderExperience\Models\PostRead;
use Modules\ReaderExperience\Models\SavedPost;
use Modules\Articles\Models\Post;
use Shared\Models\User;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

beforeEach(function () {
    $this->postInfoService = Mockery::mock(PostInfoContract::class);
    $this->commentService = Mockery::mock(CommentServiceInterface::class);
    $this->service = new DashboardService($this->postInfoService, $this->commentService);
    $this->user = User::factory()->create();
    Cache::flush();
    $this->travelTo(Carbon::parse('2026-09-03')); // Thursday of current week (start of week Monday)
});

test('getOverview composes all stats correctly', function () {
    $post1 = Post::factory()->create();
    $post2 = Post::factory()->create();

    PostRead::create([
        'user_id' => $this->user->id,
        'post_id' => $post1->id,
        'read_at' => Carbon::now()->subDay(),
    ]);
    PostRead::create([
        'user_id' => $this->user->id,
        'post_id' => $post2->id,
        'read_at' => Carbon::now()->subDays(2),
    ]);
    // Post read outside current week (should be ignored)
    PostRead::create([
        'user_id' => $this->user->id,
        'post_id' => Post::factory()->create()->id,
        'read_at' => Carbon::now()->subWeeks(2),
    ]);

    $sp1 = Post::factory()->create();
    $sp2 = Post::factory()->create();
    $sp3 = Post::factory()->create();
    SavedPost::create(['user_id' => $this->user->id, 'post_id' => $sp1->id, 'saved_at' => now()]);
    SavedPost::create(['user_id' => $this->user->id, 'post_id' => $sp2->id, 'saved_at' => now()]);
    SavedPost::create(['user_id' => $this->user->id, 'post_id' => $sp3->id, 'saved_at' => now()]);

    // Mock comment service
    $this->commentService->shouldReceive('getWeeklyCommentCountForUser')
        ->once()
        ->with($this->user->id)
        ->andReturn(5);
    $this->commentService->shouldReceive('getAllTimeTopCommenters')
        ->once()
        ->with(10)
        ->andReturn([
            ['user_id' => $this->user->id, 'name' => 'User', 'email' => 'user@example.com', 'comments_count' => 20],
            ['user_id' => 'other', 'name' => 'Other', 'email' => 'other@example.com', 'comments_count' => 10],
        ]);
    $this->commentService->shouldReceive('getTotalCommentCountForUser')
        ->once()
        ->with($this->user->id)
        ->andReturn(12);

    // Mock post info service
    $this->postInfoService->shouldReceive('getTotalReadingTimeByIds')
        ->once()
        ->with(Mockery::on(function ($ids) use ($post1, $post2) {
            return count($ids) === 2 && in_array($post1->id, $ids) && in_array($post2->id, $ids);
        }))
        ->andReturn(3600); // seconds

    $result = $this->service->getOverview($this->user->id);

    expect($result)->toMatchArray([
        'posts_read_count'   => 2,
        'total_reading_time' => 3600,
        'comments_count'     => 5,
        'is_top_commenter'   => true,
        'total_comments'     => 12,
        'total_saved_posts'  => 3,
    ]);
});

test('getOverview caches result for 5 minutes', function () {
    $this->postInfoService->shouldReceive('getTotalReadingTimeByIds')->once()->andReturn(0);
    $this->commentService->shouldReceive('getWeeklyCommentCountForUser')->once()->andReturn(0);
    $this->commentService->shouldReceive('getAllTimeTopCommenters')->once()->andReturn([]);
    $this->commentService->shouldReceive('getTotalCommentCountForUser')->once()->andReturn(0);

    $first = $this->service->getOverview($this->user->id);
    $second = $this->service->getOverview($this->user->id);

    expect($first)->toBe($second);
});

test('getRecentlyRead returns paginator with post info enriched', function () {
    $post = Post::factory()->create();
    PostRead::create(['user_id' => $this->user->id, 'post_id' => $post->id, 'read_at' => now()]);
    PostRead::create(['user_id' => $this->user->id, 'post_id' => $post->id, 'read_at' => now()->subMinute()]);
    PostRead::create(['user_id' => $this->user->id, 'post_id' => $post->id, 'read_at' => now()->subMinutes(2)]);

    $this->postInfoService->shouldReceive('getPostsByIds')
        ->once()
        ->with(Mockery::on(function ($ids) use ($post) {
            return in_array($post->id, $ids, true);
        }))
        ->andReturn([
            $post->id => (object)[
                'id' => $post->id,
                'title' => 'Test Post',
                'slug' => 'test-post',
                'featured_image' => null,
                'reading_time' => 5,
            ],
        ]);

    $paginator = $this->service->getRecentlyRead($this->user->id, 10);

    expect($paginator->total())->toBe(3);
    $item = $paginator->items()[0];
    expect($item->post_info)->toBeObject();
    expect($item->post_info->title)->toBe('Test Post');
});

test('getRecentlyRead handles empty post list without calling post info service', function () {
    $this->postInfoService->shouldNotReceive('getPostsByIds');

    $paginator = $this->service->getRecentlyRead($this->user->id);

    expect($paginator->total())->toBe(0);
});

test('getUserCommentsPaginated delegates to comment service', function () {
    $paginator = Mockery::mock(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
    $this->commentService->shouldReceive('getUserCommentsPaginated')
        ->once()
        ->with($this->user->id, 15)
        ->andReturn($paginator);

    $result = $this->service->getUserCommentsPaginated($this->user->id);

    expect($result)->toBe($paginator);
});