<?php

use Modules\About\Policies\TeamMemberPolicy;
use Modules\About\Models\TeamMember;
use Shared\Contracts\HasRolesContract;

beforeEach(function () {
    $this->policy = new TeamMemberPolicy();
});

function mockTeamMemberPolicyUser(bool $isAdmin): HasRolesContract
{
    $user = Mockery::mock(HasRolesContract::class);
    $user->shouldReceive('hasRole')->with('admin')->andReturn($isAdmin);
    return $user;
}

test('before returns true for admin', function () {
    $admin = mockTeamMemberPolicyUser(true);
    expect($this->policy->before($admin))->toBeTrue();
});

test('before returns null for non-admin', function () {
    $user = mockTeamMemberPolicyUser(false);
    expect($this->policy->before($user))->toBeNull();
});

test('viewAny returns false for non-admin', function () {
    $user = mockTeamMemberPolicyUser(false);
    expect($this->policy->viewAny($user))->toBeFalse();
});

test('view returns false for non-admin', function () {
    $user = mockTeamMemberPolicyUser(false);
    $member = Mockery::mock(TeamMember::class);
    expect($this->policy->view($user, $member))->toBeFalse();
});

test('create returns false for non-admin', function () {
    $user = mockTeamMemberPolicyUser(false);
    expect($this->policy->create($user))->toBeFalse();
});

test('update returns false for non-admin', function () {
    $user = mockTeamMemberPolicyUser(false);
    $member = Mockery::mock(TeamMember::class);
    expect($this->policy->update($user, $member))->toBeFalse();
});

test('delete returns false for non-admin', function () {
    $user = mockTeamMemberPolicyUser(false);
    $member = Mockery::mock(TeamMember::class);
    expect($this->policy->delete($user, $member))->toBeFalse();
});