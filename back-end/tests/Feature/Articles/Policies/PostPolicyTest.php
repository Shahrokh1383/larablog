<?php

use Modules\Articles\Policies\PostPolicy;
use Modules\Articles\Models\Post;
use Tests\Feature\Articles\Policies\Support\UserRoleMockFactory;

test('PostPolicy viewAny truth table', function () {
    $policy = new PostPolicy();

    expect($policy->viewAny(UserRoleMockFactory::make(['admin'])))->toBeTrue()
        ->and($policy->viewAny(UserRoleMockFactory::make(['editor'])))->toBeTrue()
        ->and($policy->viewAny(UserRoleMockFactory::make(['author'])))->toBeTrue()
        ->and($policy->viewAny(UserRoleMockFactory::make(['user'])))->toBeFalse()
        ->and($policy->viewAny(UserRoleMockFactory::make([])))->toBeFalse();
});

test('PostPolicy view truth table', function () {
    $policy = new PostPolicy();
    $ownerId = 'post-owner-id';
    $post = new Post(['user_id' => $ownerId]);

    expect($policy->view(UserRoleMockFactory::make(['admin'], 'admin-id'), $post))->toBeTrue()
        ->and($policy->view(UserRoleMockFactory::make(['editor'], 'editor-id'), $post))->toBeTrue()
        ->and($policy->view(UserRoleMockFactory::make(['author'], $ownerId), $post))->toBeTrue()
        ->and($policy->view(UserRoleMockFactory::make(['author'], 'other-id'), $post))->toBeFalse()
        ->and($policy->view(UserRoleMockFactory::make(['user'], 'user-id'), $post))->toBeFalse();
});

test('PostPolicy create truth table', function () {
    $policy = new PostPolicy();

    expect($policy->create(UserRoleMockFactory::make(['admin'])))->toBeTrue()
        ->and($policy->create(UserRoleMockFactory::make(['editor'])))->toBeTrue()
        ->and($policy->create(UserRoleMockFactory::make(['author'])))->toBeTrue()
        ->and($policy->create(UserRoleMockFactory::make(['user'])))->toBeFalse();
});

test('PostPolicy update truth table', function () {
    $policy = new PostPolicy();
    $ownerId = 'post-owner-id';
    $post = new Post(['user_id' => $ownerId]);

    expect($policy->update(UserRoleMockFactory::make(['admin'], 'admin-id'), $post))->toBeTrue()
        ->and($policy->update(UserRoleMockFactory::make(['editor'], 'editor-id'), $post))->toBeTrue()
        ->and($policy->update(UserRoleMockFactory::make(['author'], $ownerId), $post))->toBeTrue()
        ->and($policy->update(UserRoleMockFactory::make(['author'], 'other-id'), $post))->toBeFalse()
        ->and($policy->update(UserRoleMockFactory::make(['user'], 'user-id'), $post))->toBeFalse();
});

test('PostPolicy delete uses update rules', function () {
    $policy = new PostPolicy();
    $ownerId = 'post-owner-id';
    $post = new Post(['user_id' => $ownerId]);

    expect($policy->delete(UserRoleMockFactory::make(['admin'], 'admin-id'), $post))->toBeTrue()
        ->and($policy->delete(UserRoleMockFactory::make(['author'], $ownerId), $post))->toBeTrue()
        ->and($policy->delete(UserRoleMockFactory::make(['author'], 'other-id'), $post))->toBeFalse();
});

test('PostPolicy deleteImage truth table', function () {
    $policy = new PostPolicy();

    expect($policy->deleteImage(UserRoleMockFactory::make(['admin'])))->toBeTrue()
        ->and($policy->deleteImage(UserRoleMockFactory::make(['editor'])))->toBeTrue()
        ->and($policy->deleteImage(UserRoleMockFactory::make(['author'])))->toBeFalse()
        ->and($policy->deleteImage(UserRoleMockFactory::make(['user'])))->toBeFalse();
});