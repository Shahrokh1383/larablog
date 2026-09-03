<?php

use Modules\Marketing\Policies\MarketingPolicy;
use Modules\Marketing\Models\Subscriber;
use Shared\Models\User;

beforeEach(function () {
    $this->policy = new MarketingPolicy();
});

function mockUserWithRole(bool $isAdmin): User
{
    $user = Mockery::mock(User::class);
    $user->shouldReceive('hasRole')->with('admin')->andReturn($isAdmin);
    return $user;
}

test('viewAny returns true only for admin', function () {
    $admin = mockUserWithRole(true);
    $nonAdmin = mockUserWithRole(false);
    expect($this->policy->viewAny($admin))->toBeTrue();
    expect($this->policy->viewAny($nonAdmin))->toBeFalse();
});

test('view returns true only for admin', function () {
    $admin = mockUserWithRole(true);
    $nonAdmin = mockUserWithRole(false);
    $model = new Subscriber();
    expect($this->policy->view($admin, $model))->toBeTrue();
    expect($this->policy->view($nonAdmin, $model))->toBeFalse();
});

test('create returns true only for admin', function () {
    $admin = mockUserWithRole(true);
    $nonAdmin = mockUserWithRole(false);
    expect($this->policy->create($admin))->toBeTrue();
    expect($this->policy->create($nonAdmin))->toBeFalse();
});

test('update returns true only for admin', function () {
    $admin = mockUserWithRole(true);
    $nonAdmin = mockUserWithRole(false);
    $model = new Subscriber();
    expect($this->policy->update($admin, $model))->toBeTrue();
    expect($this->policy->update($nonAdmin, $model))->toBeFalse();
});

test('delete returns true only for admin', function () {
    $admin = mockUserWithRole(true);
    $nonAdmin = mockUserWithRole(false);
    $model = new Subscriber();
    expect($this->policy->delete($admin, $model))->toBeTrue();
    expect($this->policy->delete($nonAdmin, $model))->toBeFalse();
});

test('sendNewsletter returns true only for admin', function () {
    $admin = mockUserWithRole(true);
    $nonAdmin = mockUserWithRole(false);
    expect($this->policy->sendNewsletter($admin))->toBeTrue();
    expect($this->policy->sendNewsletter($nonAdmin))->toBeFalse();
});