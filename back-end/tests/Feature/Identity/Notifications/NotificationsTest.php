<?php

use Illuminate\Support\Facades\Notification;
use Modules\Identity\Models\User;
use Modules\Identity\Notifications\ResetPasswordNotification;
use Modules\Identity\Notifications\VerifyEmailNotification;

beforeEach(function () {
    config(['app.frontend_url' => 'http://localhost:3000']);
});

it('sends reset password notification via mail', function () {
    Notification::fake();
    $user = User::factory()->create();

    $user->notify(new ResetPasswordNotification('token123'));

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

it('sends verification notification via mail', function () {
    Notification::fake();
    $user = User::factory()->unverified()->create();

    $user->notify(new VerifyEmailNotification());

    Notification::assertSentTo($user, VerifyEmailNotification::class);
});