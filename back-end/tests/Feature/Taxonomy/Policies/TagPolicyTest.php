<?php

use Modules\Taxonomy\Policies\TagPolicy;
use Modules\Taxonomy\Models\Tag;
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

test('TagPolicy viewAny truth table', function () {
    $policy = new TagPolicy();

    expect($policy->viewAny(createUserMock(['admin'])))->toBeTrue()
        ->and($policy->viewAny(createUserMock(['editor'])))->toBeTrue()
        ->and($policy->viewAny(createUserMock(['author'])))->toBeTrue()
        ->and($policy->viewAny(createUserMock(['user'])))->toBeFalse()
        ->and($policy->viewAny(createUserMock([])))->toBeFalse();
});

test('TagPolicy view truth table', function () {
    $policy = new TagPolicy();
    $tag = new Tag();

    expect($policy->view(createUserMock(['admin']), $tag))->toBeTrue()
        ->and($policy->view(createUserMock(['editor']), $tag))->toBeTrue()
        ->and($policy->view(createUserMock(['author']), $tag))->toBeTrue()
        ->and($policy->view(createUserMock(['user']), $tag))->toBeFalse();
});

test('TagPolicy create truth table', function () {
    $policy = new TagPolicy();

    expect($policy->create(createUserMock(['admin'])))->toBeTrue()
        ->and($policy->create(createUserMock(['editor'])))->toBeTrue()
        ->and($policy->create(createUserMock(['author'])))->toBeFalse()
        ->and($policy->create(createUserMock(['user'])))->toBeFalse();
});

test('TagPolicy update truth table', function () {
    $policy = new TagPolicy();
    $tag = new Tag();

    expect($policy->update(createUserMock(['admin']), $tag))->toBeTrue()
        ->and($policy->update(createUserMock(['editor']), $tag))->toBeTrue()
        ->and($policy->update(createUserMock(['author']), $tag))->toBeFalse()
        ->and($policy->update(createUserMock(['user']), $tag))->toBeFalse();
});

test('TagPolicy delete truth table', function () {
    $policy = new TagPolicy();
    $tag = new Tag();

    expect($policy->delete(createUserMock(['admin']), $tag))->toBeTrue()
        ->and($policy->delete(createUserMock(['editor']), $tag))->toBeTrue()
        ->and($policy->delete(createUserMock(['author']), $tag))->toBeFalse()
        ->and($policy->delete(createUserMock(['user']), $tag))->toBeFalse();
});