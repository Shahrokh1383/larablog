<?php

use Modules\Taxonomy\Policies\CategoryPolicy;
use Modules\Taxonomy\Models\Category;
use Tests\Feature\Taxonomy\Policies\Support\UserRoleMockFactory;

test('CategoryPolicy viewAny truth table', function () {
    $policy = new CategoryPolicy();

    expect($policy->viewAny(UserRoleMockFactory::make(['admin'])))->toBeTrue()
        ->and($policy->viewAny(UserRoleMockFactory::make(['editor'])))->toBeTrue()
        ->and($policy->viewAny(UserRoleMockFactory::make(['author'])))->toBeTrue()
        ->and($policy->viewAny(UserRoleMockFactory::make(['user'])))->toBeFalse()
        ->and($policy->viewAny(UserRoleMockFactory::make([])))->toBeFalse();
});

test('CategoryPolicy view truth table', function () {
    $policy = new CategoryPolicy();
    $category = new Category();

    expect($policy->view(UserRoleMockFactory::make(['admin']), $category))->toBeTrue()
        ->and($policy->view(UserRoleMockFactory::make(['editor']), $category))->toBeTrue()
        ->and($policy->view(UserRoleMockFactory::make(['author']), $category))->toBeTrue()
        ->and($policy->view(UserRoleMockFactory::make(['user']), $category))->toBeFalse();
});

test('CategoryPolicy create truth table', function () {
    $policy = new CategoryPolicy();

    expect($policy->create(UserRoleMockFactory::make(['admin'])))->toBeTrue()
        ->and($policy->create(UserRoleMockFactory::make(['editor'])))->toBeTrue()
        ->and($policy->create(UserRoleMockFactory::make(['author'])))->toBeFalse()
        ->and($policy->create(UserRoleMockFactory::make(['user'])))->toBeFalse();
});

test('CategoryPolicy update truth table', function () {
    $policy = new CategoryPolicy();
    $category = new Category();

    expect($policy->update(UserRoleMockFactory::make(['admin']), $category))->toBeTrue()
        ->and($policy->update(UserRoleMockFactory::make(['editor']), $category))->toBeTrue()
        ->and($policy->update(UserRoleMockFactory::make(['author']), $category))->toBeFalse()
        ->and($policy->update(UserRoleMockFactory::make(['user']), $category))->toBeFalse();
});

test('CategoryPolicy delete truth table', function () {
    $policy = new CategoryPolicy();
    $category = new Category();

    expect($policy->delete(UserRoleMockFactory::make(['admin']), $category))->toBeTrue()
        ->and($policy->delete(UserRoleMockFactory::make(['editor']), $category))->toBeTrue()
        ->and($policy->delete(UserRoleMockFactory::make(['author']), $category))->toBeFalse()
        ->and($policy->delete(UserRoleMockFactory::make(['user']), $category))->toBeFalse();
});