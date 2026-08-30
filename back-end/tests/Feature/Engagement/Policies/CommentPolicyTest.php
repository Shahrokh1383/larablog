<?php

use Modules\Engagement\Policies\CommentPolicy;
use Modules\Engagement\Models\Comment;
use Shared\Models\User;

beforeEach(function () {
    $this->policy = new CommentPolicy();
});

test('create policy allows all users including guests', function () {
    expect($this->policy->create(null))->toBeTrue();
    $user = User::factory()->create();
    expect($this->policy->create($user))->toBeTrue();
});

test('delete policy allows owner or admin', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $comment = Comment::factory()->byUser($owner)->create();

    expect($this->policy->delete($owner, $comment))->toBeTrue();
    expect($this->policy->delete($other, $comment))->toBeFalse();
    expect($this->policy->delete($admin, $comment))->toBeTrue();
});

test('manage policy only allows admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $user = User::factory()->create();
    $comment = Comment::factory()->create();

    expect($this->policy->manage($admin, $comment))->toBeTrue();
    expect($this->policy->manage($user, $comment))->toBeFalse();
});