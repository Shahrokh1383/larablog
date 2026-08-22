<?php

use Modules\Taxonomy\Policies\CategoryPolicy;
use Modules\Taxonomy\Models\Category;
use Shared\Contracts\HasRolesContract;
use Mockery\MockInterface;

function createUserMock(array $roles): HasRolesContract
{
    return Mockery::mock(HasRolesContract::class, function (MockInterface $mock) use ($roles) {
        $mock->shouldReceive('hasAnyRole')
            ->andReturnUsing(fn ($checkRoles) => !empty(array_intersect((array) $checkRoles, $roles)));
        $mock->shouldReceive('hasRole')
            ->andReturnUsing(fn ($role) => in_array($role, $roles));
    });
}

test('CategoryPolicy viewAny truth table', function () {
    $policy = new CategoryPolicy();

    expect($policy->viewAny(createUserMock(['admin'])))->toBeTrue()
        ->and($policy->viewAny(createUserMock(['editor'])))->toBeTrue()
        ->and($policy->viewAny(createUserMock(['author'])))->toBeTrue()
        ->and($policy->viewAny(createUserMock(['user'])))->toBeFalse()
        ->and($policy->viewAny(createUserMock([])))->toBeFalse();
});

test('CategoryPolicy view truth table', function () {
    $policy = new CategoryPolicy();
    $category = new Category();

    expect($policy->view(createUserMock(['admin']), $category))->toBeTrue()
        ->and($policy->view(createUserMock(['editor']), $category))->toBeTrue()
        ->and($policy->view(createUserMock(['author']), $category))->toBeTrue()
        ->and($policy->view(createUserMock(['user']), $category))->toBeFalse();
});

test('CategoryPolicy create truth table', function () {
    $policy = new CategoryPolicy();

    expect($policy->create(createUserMock(['admin'])))->toBeTrue()
        ->and($policy->create(createUserMock(['editor'])))->toBeTrue()
        ->and($policy->create(createUserMock(['author'])))->toBeFalse()
        ->and($policy->create(createUserMock(['user'])))->toBeFalse();
});

test('CategoryPolicy update truth table', function () {
    $policy = new CategoryPolicy();
    $category = new Category();

    expect($policy->update(createUserMock(['admin']), $category))->toBeTrue()
        ->and($policy->update(createUserMock(['editor']), $category))->toBeTrue()
        ->and($policy->update(createUserMock(['author']), $category))->toBeFalse()
        ->and($policy->update(createUserMock(['user']), $category))->toBeFalse();
});

test('CategoryPolicy delete truth table', function () {
    $policy = new CategoryPolicy();
    $category = new Category();

    expect($policy->delete(createUserMock(['admin']), $category))->toBeTrue()
        ->and($policy->delete(createUserMock(['editor']), $category))->toBeTrue()
        ->and($policy->delete(createUserMock(['author']), $category))->toBeFalse()
        ->and($policy->delete(createUserMock(['user']), $category))->toBeFalse();
});