<?php

use Modules\About\Models\TeamMember;
use Modules\Identity\Services\Contracts\FetchesUsersByRole;
use Modules\Profile\Services\Contracts\FetchesPublicProfiles;
use Shared\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Mock cross-module interfaces
    $this->userFetcher = Mockery::mock(FetchesUsersByRole::class);
    $this->profileFetcher = Mockery::mock(FetchesPublicProfiles::class);
    $this->app->instance(FetchesUsersByRole::class, $this->userFetcher);
    $this->app->instance(FetchesPublicProfiles::class, $this->profileFetcher);

    // Create admin role and user
    Role::firstOrCreate(['name' => 'admin']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin, 'sanctum');
});

test('admin can list team members', function () {
    $user = User::factory()->create();
    TeamMember::factory()->create(['user_id' => $user->id]);

    $this->userFetcher->shouldReceive('getUsersWithRolesMap')->andReturn([
        $user->id => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'roles' => ['admin']],
    ]);
    $this->profileFetcher->shouldReceive('getPublicProfilesMap')->andReturn([
        $user->id => ['avatar' => null, 'bio' => null, 'expertise' => null, 'social_links' => null],
    ]);

    $response = $this->getJson('/api/admin/about/team-members');
    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['*' => ['id', 'user_id', 'sort_order', 'is_active', 'user']], 'meta']);
});

test('admin can list eligible users', function () {
    $user = User::factory()->create();
    $paginator = new LengthAwarePaginator(
        [['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'roles' => ['author']]],
        1,
        10,
        1
    );
    $this->userFetcher->shouldReceive('getPaginatedUsersWithRoles')
        ->once()
        ->with(['admin', 'editor', 'author'], null, 100, [])
        ->andReturn($paginator);

    $response = $this->getJson('/api/admin/about/eligible-users');
    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['*' => ['id', 'name', 'email', 'avatar', 'roles']], 'meta']);
});

test('admin can create team member', function () {
    $user = User::factory()->create();
    $this->userFetcher->shouldReceive('getUsersWithRolesMap')->andReturn([
        $user->id => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'roles' => ['admin']],
    ]);
    $this->profileFetcher->shouldReceive('getPublicProfilesMap')->andReturn([
        $user->id => ['avatar' => null, 'bio' => null, 'expertise' => null, 'social_links' => null],
    ]);

    $response = $this->postJson('/api/admin/about/team-members', [
        'user_id' => $user->id,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $response->assertStatus(201)
        ->assertJson(['message' => 'Team member created.'])
        ->assertJsonPath('data.user_id', $user->id);
    $this->assertDatabaseHas('about_team_members', ['user_id' => $user->id]);
});

test('admin can update team member', function () {
    $user = User::factory()->create();
    $member = TeamMember::factory()->create(['user_id' => $user->id, 'sort_order' => 0, 'is_active' => true]);

    $this->userFetcher->shouldReceive('getUsersWithRolesMap')->andReturn([
        $user->id => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'roles' => ['admin']],
    ]);
    $this->profileFetcher->shouldReceive('getPublicProfilesMap')->andReturn([
        $user->id => ['avatar' => null, 'bio' => null, 'expertise' => null, 'social_links' => null],
    ]);

    $response = $this->putJson("/api/admin/about/team-members/{$member->id}", [
        'sort_order' => 5,
        'is_active' => false,
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Team member updated.'])
        ->assertJsonPath('data.sort_order', 5)
        ->assertJsonPath('data.is_active', false);
    $this->assertDatabaseHas('about_team_members', ['id' => $member->id, 'sort_order' => 5, 'is_active' => false]);
});

test('admin can delete team member', function () {
    $member = TeamMember::factory()->create();
    $response = $this->deleteJson("/api/admin/about/team-members/{$member->id}");

    $response->assertStatus(200)
        ->assertJson(['message' => 'Team member deleted.']);
    $this->assertDatabaseMissing('about_team_members', ['id' => $member->id]);
});

test('non-admin cannot access admin endpoints', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->getJson('/api/admin/about/team-members');
    $response->assertStatus(403);
});