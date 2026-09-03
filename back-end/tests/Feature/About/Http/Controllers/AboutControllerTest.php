<?php

use Modules\About\Models\SiteSetting;
use Modules\About\Models\TeamMember;
use Modules\Identity\Services\Contracts\FetchesUsersByRole;
use Modules\Profile\Services\Contracts\FetchesPublicProfiles;
use Shared\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    SiteSetting::factory()->create();
    $this->userFetcher = Mockery::mock(FetchesUsersByRole::class);
    $this->profileFetcher = Mockery::mock(FetchesPublicProfiles::class);
    $this->app->instance(FetchesUsersByRole::class, $this->userFetcher);
    $this->app->instance(FetchesPublicProfiles::class, $this->profileFetcher);
});

test('GET /api/about returns settings and team members', function () {
    $user = User::factory()->create();
    TeamMember::factory()->create(['user_id' => $user->id, 'is_active' => true]);

    $this->userFetcher->shouldReceive('getUsersWithRolesMap')->andReturn([
        $user->id => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'roles' => ['admin']],
    ]);
    $this->profileFetcher->shouldReceive('getPublicProfilesMap')->andReturn([
        $user->id => ['avatar' => null, 'bio' => null, 'expertise' => null, 'social_links' => null],
    ]);

    $response = $this->getJson('/api/about');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'settings' => ['call_us_phone', 'call_us_emails', 'visit_address', 'social_links', 'story_image'],
                'team_members' => [
                    '*' => ['id', 'user_id', 'sort_order', 'is_active', 'user', 'created_at', 'updated_at'],
                ],
                'team_members_pagination' => ['current_page', 'last_page', 'per_page', 'total'],
            ],
        ]);
});