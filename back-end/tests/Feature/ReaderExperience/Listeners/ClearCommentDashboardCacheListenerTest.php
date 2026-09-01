<?php

use Modules\ReaderExperience\Listeners\ClearCommentDashboardCacheListener;
use Modules\Engagement\Events\CommentCreated;
use Modules\Engagement\Models\Comment;
use Modules\Articles\Models\Post;
use Shared\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

test('handle forgets user dashboard cache when comment has user_id', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->byUser($user)->make();
    $event = new CommentCreated($comment, null);

    Cache::put("dashboard_overview_{$user->id}", 'cached', 60);
    Cache::put('all_time_top_commenters', ['data'], 60);

    $listener = new ClearCommentDashboardCacheListener();
    $listener->handle($event);

    expect(Cache::get("dashboard_overview_{$user->id}"))->toBeNull();
    expect(Cache::get('all_time_top_commenters'))->toBeNull();
});

test('handle forgets only global cache if comment has no user_id', function () {
    $comment = Comment::factory()->make(['user_id' => null]);
    $event = new CommentCreated($comment, null);

    Cache::put('all_time_top_commenters', ['data'], 60);

    $listener = new ClearCommentDashboardCacheListener();
    $listener->handle($event);

    expect(Cache::get('all_time_top_commenters'))->toBeNull();
});