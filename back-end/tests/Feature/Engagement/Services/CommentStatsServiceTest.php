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
    
    $this->travelTo(Carbon::now()->startOfWeek()->addDays(3));
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

test('getAllTimeTopCommenters returns aggregated top commenters across registered and guest users', function () {
    $user = User::factory()->create();
    $guestEmail = 'guest@example.com';

    // Registered user comments
    Comment::factory()->approved()->count(3)->byUser($user)->create();
    // Guest comments (no user_id, but with name/email)
    Comment::factory()->approved()->count(5)->create([
        'user_id' => null,
        'name' => 'Guest',
        'email' => $guestEmail,
    ]);
    // Another registered user with 2 comments
    $otherUser = User::factory()->create();
    Comment::factory()->approved()->count(2)->byUser($otherUser)->create();

    $result = $this->statsService->getAllTimeTopCommenters(10);

    expect($result)->toHaveCount(3);

    // Find entries by identity
    $guestEntry = collect($result)->firstWhere('email', $guestEmail);
    expect($guestEntry)->not->toBeNull();
    expect($guestEntry['comments_count'])->toBe(5);
    expect($guestEntry['user_id'])->toBeNull();
    expect($guestEntry['name'])->toBe('Guest');

    $userEntry = collect($result)->firstWhere('user_id', $user->id);
    expect($userEntry)->not->toBeNull();
    expect($userEntry['comments_count'])->toBe(3);
    expect($userEntry['name'])->toBe($user->name);
    expect($userEntry['email'])->toBe($user->email);

    $otherEntry = collect($result)->firstWhere('user_id', $otherUser->id);
    expect($otherEntry)->not->toBeNull();
    expect($otherEntry['comments_count'])->toBe(2);
});