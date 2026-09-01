<?php

use Modules\ReaderExperience\Services\DashboardService;
use Shared\Models\User;
use Modules\Articles\Models\Post;
use Modules\ReaderExperience\Models\PostRead;
use Modules\ReaderExperience\Models\SavedPost;
use Modules\Engagement\Models\Comment;
use Modules\Articles\Services\Contracts\PostInfoContract;
use Modules\Engagement\Services\Contracts\CommentServiceInterface;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'sanctum');

    $this->postInfoService = Mockery::mock(PostInfoContract::class);
    $this->commentService = Mockery::mock(CommentServiceInterface::class);
    $this->app->instance(PostInfoContract::class, $this->postInfoService);
    $this->app->instance(CommentServiceInterface::class, $this->commentService);
    Cache::flush();
    $this->travelTo(Carbon::parse('2026-09-03'));
});

test('overview returns dashboard stats', function () {
    $post = Post::factory()->create();
    PostRead::factory()->create([
        'user_id' => $this->user->id,
        'post_id' => $post->id,
        'read_at' => now(),
    ]);
    SavedPost::factory()->count(2)->create(['user_id' => $this->user->id]);

    $this->postInfoService->shouldReceive('getTotalReadingTimeByIds')
        ->once()
        ->andReturn(600);
    $this->commentService->shouldReceive('getWeeklyCommentCountForUser')->once()->andReturn(3);
    $this->commentService->shouldReceive('getAllTimeTopCommenters')->once()->andReturn([]);
    $this->commentService->shouldReceive('getTotalCommentCountForUser')->once()->andReturn(5);

    $response = $this->getJson('/api/dashboard/overview');

    $response->assertOk()
        ->assertJson([
            'data' => [
                'posts_read_count' => 1,
                'total_reading_time' => 600,
                'comments_count' => 3,
                'is_top_commenter' => false,
                'total_comments' => 5,
                'total_saved_posts' => 2,
            ]
        ]);
});

test('recentlyRead returns paginated list with post info', function () {
    $post = Post::factory()->create();
    PostRead::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'post_id' => $post->id,
        'read_at' => now(),
    ]);

    $this->postInfoService->shouldReceive('getPostsByIds')
        ->once()
        ->andReturn([
            $post->id => (object)[
                'id' => $post->id,
                'title' => 'Test Post',
                'slug' => 'test-post',
                'featured_image' => null,
                'reading_time' => 4,
            ]
        ]);

    $response = $this->getJson('/api/dashboard/recently-read?per_page=10');

    $response->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.post.title', 'Test Post')
        ->assertJsonPath('meta.total', 3);
});

test('comments returns user comments paginated', function () {
    $post = Post::factory()->create();
    Comment::factory()->approved()->byUser($this->user)->count(2)->create([
        'post_id' => $post->id,
    ]);

    $this->commentService->shouldReceive('getUserCommentsPaginated')
        ->once()
        ->with($this->user->id, 15)
        ->andReturnUsing(function ($userId, $perPage) {
            return \Modules\Engagement\Models\Comment::where('user_id', $userId)->paginate($perPage);
        });

    $response = $this->getJson('/api/dashboard/comments');

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.post.title', $post->title); // assuming post_slug/post_title attached by service
});