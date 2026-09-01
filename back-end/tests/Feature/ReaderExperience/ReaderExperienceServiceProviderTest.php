<?php

use Modules\ReaderExperience\Services\Contracts\SavedPostInteractionContract;
use Modules\ReaderExperience\Services\SavedPostService;
use Modules\ReaderExperience\Listeners\ClearCommentDashboardCacheListener;
use Modules\Engagement\Events\CommentCreated;
use Illuminate\Support\Facades\Event;

test('service provider binds SavedPostInteractionContract to SavedPostService', function () {
    $this->assertTrue(app()->bound(SavedPostInteractionContract::class));
    $resolved = app(SavedPostInteractionContract::class);
    expect($resolved)->toBeInstanceOf(SavedPostService::class);
});

test('service provider listens to CommentCreated event', function () {
    Event::assertListening(
        CommentCreated::class,
        ClearCommentDashboardCacheListener::class
    );
});