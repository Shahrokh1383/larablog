<?php

use Modules\Identity\Events\UserDeleted;
use Modules\Identity\Events\UserNameUpdated;
use Modules\Identity\Events\UserPasswordUpdated;
use Modules\Identity\Events\UserRoleUpdated;
use Modules\Identity\Models\User;

it('UserDeleted contains user id', function () {
    $event = new UserDeleted('uuid-123');
    expect($event->userId)->toBe('uuid-123');
});

it('UserNameUpdated contains user', function () {
    $user = User::factory()->make();
    $event = new UserNameUpdated($user);
    expect($event->user)->toBe($user);
});

it('UserPasswordUpdated contains user', function () {
    $user = User::factory()->make();
    $event = new UserPasswordUpdated($user);
    expect($event->user)->toBe($user);
});

it('UserRoleUpdated contains user and role', function () {
    $user = User::factory()->make();
    $event = new UserRoleUpdated($user, 'admin');
    expect($event->user)->toBe($user);
    expect($event->role)->toBe('admin');
});