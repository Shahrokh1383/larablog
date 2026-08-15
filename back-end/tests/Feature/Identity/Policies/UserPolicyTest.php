<?php

use Modules\Identity\Models\User;
use Modules\Identity\Policies\UserPolicy;

beforeEach(function () {
    $this->policy = new UserPolicy();
});

it('allows admins all abilities via before', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect($this->policy->before($admin, 'any'))->toBeTrue();
});

it('denies non-admins viewAny', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    expect($this->policy->viewAny($user))->toBeFalse();
});

it('allows admins updateRole', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $target = User::factory()->create();

    expect($this->policy->updateRole($admin, $target))->toBeTrue();
});

it('denies non-admins updateRole', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $target = User::factory()->create();

    expect($this->policy->updateRole($user, $target))->toBeFalse();
});

it('allows user to update own profile', function () {
    $user = User::factory()->create();

    expect($this->policy->update($user, $user))->toBeTrue();
});

it('denies user to update other profile', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    expect($this->policy->update($user, $other))->toBeFalse();
});

it('denies non-admin delete', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $target = User::factory()->create();

    expect($this->policy->delete($user, $target))->toBeFalse();
});