<?php

use Modules\Engagement\Listeners\SendCommentNotifications;
use Modules\Engagement\Events\CommentCreated;
use Modules\Engagement\Models\Comment;
use Modules\Articles\Models\Post;
use Shared\Models\User;
use Modules\Articles\Services\Contracts\PostInfoContract;
use Modules\Engagement\Notifications\NewCommentOnPost;
use Modules\Engagement\Notifications\NewReplyToComment;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->post = Post::factory()->create();
    $this->postInfoService = Mockery::mock(PostInfoContract::class);
    $this->listener = new SendCommentNotifications($this->postInfoService);
});

test('it sends notification to post author when comment created by another user', function () {
    Notification::fake();

    $authorId = User::factory()->create()->getKey();
    $commenterId = User::factory()->create()->getKey();

    $author = User::query()->findOrFail($authorId);
    $commenter = User::query()->findOrFail($commenterId);

    $post = Post::factory()->create(['user_id' => $author->id]);

    $this->postInfoService->shouldReceive('getPostInfo')
        ->once()
        ->with($post->id)
        ->andReturn((object)[
            'authorId' => $author->id,
            'title'    => 'Post Title',
            'slug'     => 'post-slug',
        ]);

    $comment = Comment::factory()->byUser($commenter)->create(['post_id' => $post->id]);

    $event = new CommentCreated($comment);
    $this->listener->handle($event);

    Notification::assertSentTo($author, NewCommentOnPost::class);
    Notification::assertNotSentTo($commenter, NewCommentOnPost::class);
});

test('it does not notify post author if commenter is the author', function () {
    Notification::fake();

    $authorId = User::factory()->create()->getKey();
    $author = User::query()->findOrFail($authorId);

    $post = Post::factory()->create(['user_id' => $author->id]);

    $this->postInfoService->shouldReceive('getPostInfo')
        ->once()
        ->with($post->id)
        ->andReturn((object)[
            'authorId' => $author->id,
            'title'    => 'Post Title',
            'slug'     => 'post-slug',
        ]);

    $comment = Comment::factory()->byUser($author)->create(['post_id' => $post->id]);

    $this->listener->handle(new CommentCreated($comment));

    Notification::assertNothingSent();
});

test('it sends reply notification to parent comment author for direct reply', function () {
    Notification::fake();

    $authorId = User::factory()->create()->getKey();
    $parentCommenterId = User::factory()->create()->getKey();
    $replierId = User::factory()->create()->getKey();

    $author = User::query()->findOrFail($authorId);
    $parentCommenter = User::query()->findOrFail($parentCommenterId);
    $replier = User::query()->findOrFail($replierId);

    $parent = Comment::factory()->byUser($parentCommenter)->create(['post_id' => $this->post->id]);
    $reply = Comment::factory()->byUser($replier)->replyTo($parent)->create();

    $this->postInfoService->shouldReceive('getPostInfo')
        ->once()
        ->with($this->post->id)
        ->andReturn((object)[
            'authorId' => $author->id,
            'title'    => 'Post',
            'slug'     => 'post-slug',
        ]);

    // Direct reply: event created without originalParentId, listener falls back to comment->parent_id
    $this->listener->handle(new CommentCreated($reply));

    Notification::assertSentTo($parentCommenter, NewReplyToComment::class);
    Notification::assertNotSentTo($replier, NewReplyToComment::class);
});

test('it sends reply notification to immediate parent author for nested reply', function () {
    Notification::fake();

    $authorId = User::factory()->create()->getKey();
    $topLevelCommenterId = User::factory()->create()->getKey();
    $firstReplierId = User::factory()->create()->getKey();
    $nestedReplierId = User::factory()->create()->getKey();

    $author = User::query()->findOrFail($authorId);
    $topLevelCommenter = User::query()->findOrFail($topLevelCommenterId);
    $firstReplier = User::query()->findOrFail($firstReplierId);
    $nestedReplier = User::query()->findOrFail($nestedReplierId);

    // Top-level comment by topLevelCommenter
    $topLevel = Comment::factory()->byUser($topLevelCommenter)->create(['post_id' => $this->post->id]);
    // First reply (direct) by firstReplier
    $firstReply = Comment::factory()->byUser($firstReplier)->replyTo($topLevel)->create();
    // Nested reply to firstReply (would be flattened by service), but event preserves originalParentId
    $nestedReply = Comment::factory()->byUser($nestedReplier)->replyTo($firstReply)->create();

    $this->postInfoService->shouldReceive('getPostInfo')
        ->once()
        ->with($this->post->id)
        ->andReturn((object)[
            'authorId' => $author->id,
            'title'    => 'Post',
            'slug'     => 'post-slug',
        ]);

    // Simulate event with originalParentId = firstReply->id
    $this->listener->handle(new CommentCreated($nestedReply, $firstReply->id));

    Notification::assertSentTo($firstReplier, NewReplyToComment::class);
    Notification::assertNotSentTo($topLevelCommenter, NewReplyToComment::class);
});

test('it aborts if post info not found', function () {
    Notification::fake();

    $comment = Comment::factory()->create();

    $this->postInfoService->shouldReceive('getPostInfo')
        ->once()
        ->with($comment->post_id)
        ->andReturnNull();

    $this->listener->handle(new CommentCreated($comment));

    Notification::assertNothingSent();
});