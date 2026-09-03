<?php

use Modules\About\Policies\SiteSettingPolicy;
use Shared\Contracts\HasRolesContract;

beforeEach(function () {
    $this->policy = new SiteSettingPolicy();
});

function mockSiteSettingPolicyUser(bool $isAdmin): HasRolesContract
{
    $user = Mockery::mock(HasRolesContract::class);
    $user->shouldReceive('hasRole')->with('admin')->andReturn($isAdmin);
    return $user;
}

test('before returns true for admin', function () {
    $admin = mockSiteSettingPolicyUser(true);
    expect($this->policy->before($admin))->toBeTrue();
});

test('before returns null for non-admin', function () {
    $user = mockSiteSettingPolicyUser(false);
    expect($this->policy->before($user))->toBeNull();
});

test('view returns false for non-admin (before not bypass)', function () {
    $user = mockSiteSettingPolicyUser(false);
    expect($this->policy->view($user))->toBeFalse();
});

test('update returns false for non-admin', function () {
    $user = mockSiteSettingPolicyUser(false);
    expect($this->policy->update($user))->toBeFalse();
});