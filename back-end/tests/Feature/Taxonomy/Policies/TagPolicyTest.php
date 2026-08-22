<?php

use Modules\Taxonomy\Policies\TagPolicy;
use Modules\Taxonomy\Models\Tag;
use Tests\Feature\Taxonomy\Policies\Support\UserRoleMockFactory;

test('TagPolicy viewAny truth table', function () {
    $policy = new TagPolicy();

    expect($policy->viewAny(UserRoleMockFactory::make(['admin'])))->toBeTrue()
        ->and($policy->viewAny(UserRoleMockFactory::make(['editor'])))->toBeTrue()
        ->and($policy->viewAny(UserRoleMockFactory::make(['author'])))->toBeTrue()
        ->and($policy->viewAny(UserRoleMockFactory::make(['user'])))->toBeFalse()
        ->and($policy->viewAny(UserRoleMockFactory::make([])))->toBeFalse();
});

test('TagPolicy view truth table', function () {
    $policy = new TagPolicy();
    $tag = new Tag();

    expect($policy->view(UserRoleMockFactory::make(['admin']), $tag))->toBeTrue()
        ->and($policy->view(UserRoleMockFactory::make(['editor']), $tag))->toBeTrue()
        ->and($policy->view(UserRoleMockFactory::make(['author']), $tag))->toBeTrue()
        ->and($policy->view(UserRoleMockFactory::make(['user']), $tag))->toBeFalse();
});

test('TagPolicy create truth table', function () {
    $policy = new TagPolicy();

    expect($policy->create(UserRoleMockFactory::make(['admin'])))->toBeTrue()
        ->and($policy->create(UserRoleMockFactory::make(['editor'])))->toBeTrue()
        ->and($policy->create(UserRoleMockFactory::make(['author'])))->toBeFalse()
        ->and($policy->create(UserRoleMockFactory::make(['user'])))->toBeFalse();
});

test('TagPolicy update truth table', function () {
    $policy = new TagPolicy();
    $tag = new Tag();

    expect($policy->update(UserRoleMockFactory::make(['admin']), $tag))->toBeTrue()
        ->and($policy->update(UserRoleMockFactory::make(['editor']), $tag))->toBeTrue()
        ->and($policy->update(UserRoleMockFactory::make(['author']), $tag))->toBeFalse()
        ->and($policy->update(UserRoleMockFactory::make(['user']), $tag))->toBeFalse();
});

test('TagPolicy delete truth table', function () {
    $policy = new TagPolicy();
    $tag = new Tag();

    expect($policy->delete(UserRoleMockFactory::make(['admin']), $tag))->toBeTrue()
        ->and($policy->delete(UserRoleMockFactory::make(['editor']), $tag))->toBeTrue()
        ->and($policy->delete(UserRoleMockFactory::make(['author']), $tag))->toBeFalse()
        ->and($policy->delete(UserRoleMockFactory::make(['user']), $tag))->toBeFalse();
});