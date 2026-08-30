<?php

use Modules\Engagement\Services\CommentService;
use Modules\Engagement\DTOs\CommentCreateDTO;
use Modules\Engagement\Models\Comment;
use Modules\Articles\Models\Post;
use Shared\Models\User;
use Modules\Articles\Services\Contracts\PostAdminServiceInterface;
use Modules\Engagement\Events\CommentCreated;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->mockPostAdminService = Mockery::mock(PostAdminServiceInterface::class);
    $this->app->instance(PostAdminServiceInterface::class, $this->mockPostAdminService);
    $this->commentService = new CommentService($this->mockPostAdminService);
    $this->post = Post::factory()->create();
});

test('create as guest creates unapproved comment and dispatches event', function () {
    Event::fake([CommentCreated::class]);

    $dto = new CommentCreateDTO(
        postId: $this->post->id,
        body: 'Guest comment',
        name: 'Guest',
        email: 'guest@example.com',
        userId: null,
    );

    $comment = $this->commentService->create($dto);

    expect($comment)->toBeInstanceOf(Comment::class);
    $this->assertDatabaseHas('engagement_comments', [
        'id' => $comment->id,
        'post_id' => $this->post->id,
        'is_approved' => false,
        'name' => 'Guest',
    ]);
    Event::assertDispatched(CommentCreated::class);
});

test('create as authenticated user creates approved comment and flattens nested parent', function () {
    Event::fake([CommentCreated::class]);
    $user = User::factory()->create();
    $parent = Comment::factory()->byUser($user)->approved()->create(['post_id' => $this->post->id]);
    $reply = Comment::factory()->byUser($user)->approved()->replyTo($parent)->create();

    $dto = new CommentCreateDTO(
        postId: $this->post->id,
        body: 'Reply to reply',
        parentId: $reply->id,
        userId: $user->id,
    );

    $comment = $this->commentService->create($dto);

    expect($comment->parent_id)->toBe($parent->id);
    expect($comment->is_approved)->toBeTrue();

    // Verify that the event carries the original parent ID (the immediate reply)
    Event::assertDispatched(CommentCreated::class, function (CommentCreated $event) use ($comment, $reply) {
        return $event->comment->id === $comment->id &&
               $event->originalParentId === $reply->id;
    });
});

test('approve method updates comment', function () {
    $comment = Comment::factory()->create(['is_approved' => false]);
    $this->commentService->approve($comment);
    $this->assertDatabaseHas('engagement_comments', ['id' => $comment->id, 'is_approved' => true]);
});

test('delete top-level comment cascades replies', function () {
    $parent = Comment::factory()->create();
    $reply = Comment::factory()->replyTo($parent)->create();

    $this->commentService->delete($parent);

    $this->assertDatabaseMissing('engagement_comments', ['id' => $parent->id]);
    $this->assertDatabaseMissing('engagement_comments', ['id' => $reply->id]);
});

test('getUnreadCount returns count of unapproved comments', function () {
    Comment::factory()->count(4)->create(['is_approved' => false]);
    Comment::factory()->count(2)->create(['is_approved' => true]);
    expect($this->commentService->getUnreadCount())->toBe(4);
});

test('getCommentsForPostAdmin calls PostAdminService and paginates', function () {
    $user = User::factory()->create();
    $comments = Comment::factory()->count(5)->create(['post_id' => $this->post->id]);

    $this->mockPostAdminService->shouldReceive('findViewablePostId')
        ->once()
        ->with($this->post->id, $user)
        ->andReturn($this->post->id);

    $paginator = $this->commentService->getCommentsForPostAdmin($this->post->id, $user, 10);
    expect($paginator->total())->toBe(5);
});

test('createCommentForPost resolves post ID and creates staff reply', function () {
    $user = User::factory()->create();
    $this->mockPostAdminService->shouldReceive('findViewablePostId')
        ->once()
        ->with($this->post->id, $user)
        ->andReturn($this->post->id);

    $comment = $this->commentService->createCommentForPost(
        postIdentifier: $this->post->id,
        user: $user,
        body: 'Staff reply',
    );

    $this->assertDatabaseHas('engagement_comments', [
        'post_id' => $this->post->id,
        'user_id' => $user->id,
        'is_approved' => true,
    ]);
});