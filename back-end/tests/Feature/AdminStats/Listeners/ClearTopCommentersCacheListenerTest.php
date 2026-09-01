<?php

use Modules\AdminStats\Listeners\ClearTopCommentersCacheListener;
use Modules\AdminStats\Services\ContentStatsService;
use Modules\Engagement\Events\CommentCreated;
use Modules\Engagement\Models\Comment;
use Illuminate\Support\Facades\Cache;

test('handle forgets top commenters cache key', function () {
    Cache::shouldReceive('forget')
        ->once()
        ->with(ContentStatsService::TOP_COMMENTERS_CACHE_KEY);

    $comment = Comment::factory()->make();
    $event = new CommentCreated($comment, null);

    $listener = new ClearTopCommentersCacheListener();
    $listener->handle($event);
});