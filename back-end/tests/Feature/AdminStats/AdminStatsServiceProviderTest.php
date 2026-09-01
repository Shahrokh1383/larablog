<?php

use Modules\AdminStats\Services\Contracts\ContentStatsContract;
use Modules\AdminStats\Services\ContentStatsService;
use Modules\AdminStats\Policies\AdminStatsPolicy;
use Modules\AdminStats\Listeners\ClearTopCommentersCacheListener;
use Modules\Engagement\Events\CommentCreated;
use Modules\Engagement\Models\Comment;
use Shared\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;

test('service provider binds ContentStatsContract to ContentStatsService', function () {
    $this->assertTrue(
        app()->bound(ContentStatsContract::class)
    );

    $resolved = app(ContentStatsContract::class);
    expect($resolved)->toBeInstanceOf(ContentStatsService::class);
});

test('service provider registers gates with correct policy methods', function () {
    $this->assertTrue(Gate::has('viewAdminDashboard'));
    $this->assertTrue(Gate::has('viewAdminAuthors'));
    $this->assertTrue(Gate::has('viewAuthorDashboard'));
    $this->assertTrue(Gate::has('viewAdminTopCommenters'));

    $policy = new AdminStatsPolicy();
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect($policy->viewDashboard($admin))->toBeTrue();
    expect($policy->viewAuthors($admin))->toBeTrue();
    expect($policy->viewAuthorDashboard($admin))->toBeTrue();
    expect($policy->viewAdminTopCommenters($admin))->toBeTrue();
});

test('service provider listens to CommentCreated event', function () {
    Event::fake();

    // Use a bare Comment instance to avoid factory side-effects (like user creation)
    $comment = new Comment();
    event(new CommentCreated($comment, null));

    Event::assertListening(
        CommentCreated::class,
        ClearTopCommentersCacheListener::class
    );
});