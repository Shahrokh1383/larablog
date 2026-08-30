<?php

use Modules\Engagement\Models\Comment;
use Modules\Articles\Models\Post;
use Shared\Models\User;
use Modules\Articles\Services\Contracts\PostAdminServiceInterface;
use Modules\Articles\Services\Contracts\PostInfoContract;
use Modules\Engagement\Services\CommentService;
use Illuminate\Support\Facades\Notification;
use Modules\Engagement\Notifications\NewCommentOnPost;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->post = Post::factory()->create(['user_id' => User::factory()]);

    $this->mockPostAdminService = Mockery::mock(PostAdminServiceInterface::class);
    $this->mockPostAdminService->shouldReceive('findViewablePostId')
        ->andReturnUsing(fn ($id, $user) => $id === $this->post->id ? $this->post->id : null);
    $this->app->instance(PostAdminServiceInterface::class, $this->mockPostAdminService);

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
});

test('unreadCount returns count of unapproved comments', function () {
    Comment::factory()->count(3)->create(['is_approved' => false]);
    Comment::factory()->count(2)->create(['is_approved' => true]);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/admin/comments/unread-count');

    $response->assertOk()
        ->assertJson(['unread_count' => 3]);
});

test('approve comment requires manage ability and updates status', function () {
    $comment = Comment::factory()->create(['is_approved' => false]);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->patchJson("/api/admin/comments/{$comment->id}/approve");

    $response->assertOk()
        ->assertJson(['message' => 'Comment approved']);

    $this->assertDatabaseHas('engagement_comments', [
        'id' => $comment->id,
        'is_approved' => true,
    ]);
});

test('delete comment with admin role works and removes replies if top-level', function () {
    $parent = Comment::factory()->approved()->create(['post_id' => $this->post->id]);
    $reply = Comment::factory()->approved()->replyTo($parent)->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->deleteJson("/api/admin/comments/{$parent->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('engagement_comments', ['id' => $parent->id]);
    $this->assertDatabaseMissing('engagement_comments', ['id' => $reply->id]);
});

test('index returns paginated comments for a post with proper resource', function () {
    $comments = Comment::factory()->count(5)->approved()->create(['post_id' => $this->post->id]);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson("/api/admin/posts/{$this->post->id}/comments?per_page=2");

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'post_id', 'parent_id', 'body', 'is_approved', 'author', 'created_at'],
            ],
            'links', 'meta',
        ]);
    $this->assertCount(2, $response->json('data'));
});

test('store creates a staff reply and returns 201 with resource', function () {
    Notification::fake();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson("/api/admin/posts/{$this->post->id}/comments", [
            'body' => 'Admin reply',
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['data' => ['id', 'body', 'is_approved', 'author']]);

    $this->assertDatabaseHas('engagement_comments', [
        'post_id' => $this->post->id,
        'user_id' => $this->admin->id,
        'body' => 'Admin reply',
        'is_approved' => true,
    ]);

    Notification::assertSentTo($this->post->user, NewCommentOnPost::class);
});