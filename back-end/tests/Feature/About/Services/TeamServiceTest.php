<?php

use Modules\About\Services\TeamService;
use Modules\About\Models\TeamMember;
use Modules\About\DTOs\TeamMemberDTO;
use Modules\Identity\Services\Contracts\FetchesUsersByRole;
use Modules\Profile\Services\Contracts\FetchesPublicProfiles;
use Shared\Models\User;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->userFetcher = Mockery::mock(FetchesUsersByRole::class);
    $this->profileFetcher = Mockery::mock(FetchesPublicProfiles::class);
    $this->service = new TeamService($this->userFetcher, $this->profileFetcher);
});

function mockUserFetcher(array $usersMap, ?Paginator $paginator = null)
{
    $fetcher = Mockery::mock(FetchesUsersByRole::class);
    $fetcher->shouldReceive('getUsersWithRolesMap')->andReturn($usersMap);
    if ($paginator !== null) {
        $fetcher->shouldReceive('getPaginatedUsersWithRoles')->andReturn($paginator);
    }
    return $fetcher;
}

function mockProfileFetcher(array $profilesMap)
{
    $fetcher = Mockery::mock(FetchesPublicProfiles::class);
    $fetcher->shouldReceive('getPublicProfilesMap')->andReturn($profilesMap);
    return $fetcher;
}

test('getActiveMembersData returns only active, sorted, mapped', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $member1 = TeamMember::factory()->create(['user_id' => $user1->id, 'sort_order' => 2, 'is_active' => true]);
    $member2 = TeamMember::factory()->create(['user_id' => $user2->id, 'sort_order' => 1, 'is_active' => true]);
    TeamMember::factory()->inactive()->create(['user_id' => User::factory()->create()->id]);

    $usersMap = [
        $user1->id => ['id' => $user1->id, 'name' => $user1->name, 'email' => $user1->email, 'roles' => ['admin']],
        $user2->id => ['id' => $user2->id, 'name' => $user2->name, 'email' => $user2->email, 'roles' => ['editor']],
    ];
    $profilesMap = [
        $user1->id => ['avatar' => 'avatar1.jpg', 'bio' => 'Bio1', 'expertise' => 'Expert1', 'social_links' => []],
        $user2->id => ['avatar' => 'avatar2.jpg', 'bio' => 'Bio2', 'expertise' => 'Expert2', 'social_links' => []],
    ];

    $this->userFetcher->shouldReceive('getUsersWithRolesMap')->andReturn($usersMap);
    $this->profileFetcher->shouldReceive('getPublicProfilesMap')->andReturn($profilesMap);

    $result = $this->service->getActiveMembersData(2);
    expect($result['data'])->toHaveCount(2);
    // Sort order: member2 first (sort_order 1), then member1 (sort_order 2)
    expect($result['data'][0]['id'])->toBe($member2->id);
    expect($result['data'][1]['id'])->toBe($member1->id);
    expect($result['meta']['total'])->toBe(2);
});

test('getMembersForAdminData returns all members sorted/paginated', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    TeamMember::factory()->create(['user_id' => $user1->id, 'sort_order' => 2]);
    TeamMember::factory()->create(['user_id' => $user2->id, 'sort_order' => 1]);

    $this->userFetcher->shouldReceive('getUsersWithRolesMap')->andReturn([]);
    $this->profileFetcher->shouldReceive('getPublicProfilesMap')->andReturn([]);

    $result = $this->service->getMembersForAdminData(2);
    expect($result['data'])->toHaveCount(2);
    expect($result['meta']['total'])->toBe(2);
});

test('getEligibleUsersData returns mapped users excluding existing members', function () {
    $existingUser = User::factory()->create();
    TeamMember::factory()->create(['user_id' => $existingUser->id]);

    $eligibleUser = User::factory()->create();
    $paginator = new Paginator(
        [['id' => $eligibleUser->id, 'name' => $eligibleUser->name, 'email' => $eligibleUser->email, 'roles' => ['author']]],
        1,
        10,
        1
    );
    $this->userFetcher->shouldReceive('getPaginatedUsersWithRoles')
        ->once()
        ->with(['admin', 'editor', 'author'], null, 10, [$existingUser->id])
        ->andReturn($paginator);

    $result = $this->service->getEligibleUsersData(null, 10);
    expect($result['data'])->toHaveCount(1);
    expect($result['data'][0]['id'])->toBe($eligibleUser->id);
    expect($result['meta']['total'])->toBe(1);
});

test('create creates member and returns mapped data', function () {
    $user = User::factory()->create();
    $dto = new TeamMemberDTO(userId: $user->id, sortOrder: 5, isActive: true);

    $this->userFetcher->shouldReceive('getUsersWithRolesMap')->andReturn([
        $user->id => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'roles' => ['admin']],
    ]);
    $this->profileFetcher->shouldReceive('getPublicProfilesMap')->andReturn([
        $user->id => ['avatar' => 'avatar.jpg', 'bio' => 'Bio', 'expertise' => 'Exp', 'social_links' => []],
    ]);

    $result = $this->service->create($dto);

    $this->assertDatabaseHas('about_team_members', [
        'user_id' => $user->id,
        'sort_order' => 5,
        'is_active' => true,
    ]);
    expect($result['user_id'])->toBe($user->id);
    expect($result['user']['id'])->toBe($user->id);
    expect($result['sort_order'])->toBe(5);
    expect($result['is_active'])->toBeTrue();
});

test('update updates member and returns mapped data', function () {
    $user = User::factory()->create();
    $member = TeamMember::factory()->create(['user_id' => $user->id, 'sort_order' => 1, 'is_active' => true]);
    $dto = new TeamMemberDTO(userId: null, sortOrder: 3, isActive: false);

    $this->userFetcher->shouldReceive('getUsersWithRolesMap')->andReturn([
        $user->id => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'roles' => ['admin']],
    ]);
    $this->profileFetcher->shouldReceive('getPublicProfilesMap')->andReturn([
        $user->id => ['avatar' => null, 'bio' => null, 'expertise' => null, 'social_links' => null],
    ]);

    $result = $this->service->update($member, $dto);

    $member->refresh();
    expect($member->sort_order)->toBe(3);
    expect($member->is_active)->toBeFalse();
    expect($result['sort_order'])->toBe(3);
    expect($result['is_active'])->toBeFalse();
});

test('delete deletes member', function () {
    $member = TeamMember::factory()->create();
    $this->service->delete($member);
    $this->assertDatabaseMissing('about_team_members', ['id' => $member->id]);
});