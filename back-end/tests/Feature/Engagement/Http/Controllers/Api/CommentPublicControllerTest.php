<?php

use Modules\Engagement\Models\Comment;
use Modules\Articles\Models\Post;
use Shared\Models\User;
use Modules\Profile\Services\Contracts\FetchesPublicProfiles;
use Modules\Engagement\Services\CommentPublicService;
use Modules\Engagement\Services\CommentService;
use Modules\Articles\Services\Contracts\PostAdminServiceInterface;
use Modules\Articles\Services\Contracts\PostInfoContract;
use Illuminate\Support\Facades\Notification;
use Modules\Engagement\Notifications\NewCommentOnPost;

beforeEach(function () {
    $this->post = Post::factory()->create(['user_id' => User::factory()]);

    // Mock dependencies
    $this->mockPostAdminService = Mockery::mock(PostAdminServiceInterface::class);
    $this->mockPostAdminService->shouldReceive('findViewablePostId')
        ->andReturnUsing(fn ($id, $user) => $id === $this->post->id ? $this->post->id : null);
    $this->app->instance(PostAdminServiceInterface::class, $this->mockPostAdminService);

    $this->mockProfileFetcher = Mockery::mock(FetchesPublicProfiles::class);
    $this->mockProfileFetcher->shouldReceive('getPublicProfilesMap')
        ->andReturnUsing(fn ($ids) => array_fill_keys($ids, ['avatar' => null]));
    $this->app->instance(FetchesPublicProfiles::class, $this->mockProfileFetcher);

    $this->mockPostInfo = Mockery::mock(PostInfoContract::class);
    $this->mockPostInfo->shouldReceive('getPostInfo')
        ->andReturnUsing(function ($postId) {
            $post = Post::find($postId);
            return $post ? (object)[
                'authorId' => $post->user_id,
                'title'    => $post->title,
                'slug'     => $post->slug,
            ] : null;
        });
    $this->app->instance(PostInfoContract::class, $this->mockPostInfo);

    $this->commentService = new CommentService($this->mockPostAdminService);
    $this->app->instance(CommentService::class, $this->commentService);

    $this->publicService = new CommentPublicService($this->mockProfileFetcher);
    $this->app->instance(CommentPublicService::class, $this->publicService);
});

test('index returns paginated top-level comments with cursor and meta', function () {
    $comments = Comment::factory()->count(20)->approved()->create(['post_id' => $this->post->id, 'parent_id' => null]);

    $response = $this->getJson("/api/posts/{$this->post->id}/comments");

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'post_id', 'parent_id', 'body', 'is_approved', 'author', 'replies', 'replies_count', 'replies_has_more', 'created_at'],
            ],
            'meta' => ['total', 'next_cursor', 'has_more'],
        ]);

    $this->assertCount(15, $response->json('data'));
    expect($response->json('meta.total'))->toBe(20);
    expect($response->json('meta.has_more'))->toBeTrue();
});

test('replies returns paginated replies for a comment', function () {
    $parent = Comment::factory()->approved()->create(['post_id' => $this->post->id]);
    $replies = Comment::factory()->count(15)->approved()->replyTo($parent)->create();

    $response = $this->getJson("/api/comments/{$parent->id}/replies?skip=2&take=5");

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'post_id', 'parent_id', 'body', 'is_approved', 'author', 'replies_count', 'replies_has_more', 'created_at'],
            ],
            'meta' => ['has_more'],
        ]);

    $this->assertCount(5, $response->json('data'));
    expect($response->json('meta.has_more'))->toBeTrue();
});

test('store as guest creates unapproved comment and returns 201', function () {
    Notification::fake();

    $response = $this->postJson('/api/comments', [
        'post_id' => $this->post->id,
        'body' => 'Guest comment',
        'name' => 'Guest',
        'email' => 'guest@example.com',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['data' => ['id', 'body', 'is_approved', 'author']]);

    $comment = Comment::first();
    expect($comment->is_approved)->toBeFalse();
    expect($comment->name)->toBe('Guest');

    Notification::assertSentTo($this->post->user, NewCommentOnPost::class);
});

test('store as authenticated user creates approved comment and returns 201', function () {
    Notification::fake();
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/comments', [
            'post_id' => $this->post->id,
            'body' => 'Auth comment',
        ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('engagement_comments', [
        'post_id' => $this->post->id,
        'user_id' => $user->id,
        'is_approved' => true,
    ]);

    Notification::assertSentTo($this->post->user, NewCommentOnPost::class);
});

test('destroy requires authentication and delete ability', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->byUser($user)->create();

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/comments/{$comment->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('engagement_comments', ['id' => $comment->id]);
});