<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Identity\Services\Contracts\DeletesUserAccount;
use Modules\Identity\Services\Contracts\UpdatesUserBasicInfo;
use Modules\Profile\Models\Profile;
use Modules\Profile\Services\Contracts\ProfileServiceInterface;
use Modules\Profile\Services\ProfileService;
use Shared\Models\User;

beforeEach(function () {
    Storage::fake('public');

    $this->mockUpdatesUserBasicInfo = Mockery::mock(UpdatesUserBasicInfo::class);
    $this->mockDeletesUserAccount = Mockery::mock(DeletesUserAccount::class);

    $this->app->instance(UpdatesUserBasicInfo::class, $this->mockUpdatesUserBasicInfo);
    $this->app->instance(DeletesUserAccount::class, $this->mockDeletesUserAccount);

    // Bind real ProfileService with mocked dependencies
    $profileService = new ProfileService(
        $this->mockUpdatesUserBasicInfo,
        $this->mockDeletesUserAccount
    );
    $this->app->instance(ProfileServiceInterface::class, $profileService);

    $this->user = User::factory()->create();
});

test('show returns profile for authenticated user', function () {
    $profile = Profile::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/profile');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'id', 'user_id', 'name', 'username', 'avatar', 'bio',
                'expertise', 'years_of_experience', 'social_links',
                'posts_count', 'total_views', 'created_at', 'updated_at',
            ],
        ])
        ->assertJsonPath('data.user_id', $this->user->id);
});

test('show creates profile if missing and returns it', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/profile');

    $response->assertOk();
    $this->assertDatabaseHas('profiles', ['user_id' => $this->user->id]);
});

test('update profile calls identity service for name and updates profile fields', function () {
    $profile = Profile::factory()->create(['user_id' => $this->user->id]);

    $this->mockUpdatesUserBasicInfo
        ->shouldReceive('updateName')
        ->once()
        ->with(Mockery::on(fn ($user) => $user->id === $this->user->id), 'New Name');

    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson('/api/profile', [
            'name' => 'New Name',
            'bio' => 'Updated bio',
            'expertise' => 'PHP',
            'years_of_experience' => 10,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.bio', 'Updated bio')
        ->assertJsonPath('data.expertise', 'PHP')
        ->assertJsonPath('data.years_of_experience', 10);

    $this->assertDatabaseHas('profiles', [
        'user_id' => $this->user->id,
        'bio' => 'Updated bio',
        'expertise' => 'PHP',
        'years_of_experience' => 10,
    ]);
});

test('upload avatar stores file and returns url', function () {
    $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/profile/upload-avatar', [
            'avatar' => $file,
        ]);

    $response->assertOk()
        ->assertJsonStructure(['url']);

    $url = $response->json('url');
    expect($url)->toContain("/storage/profiles/avatars/{$this->user->id}/");

    Storage::disk('public')->assertExists(
        str_replace('/storage/', '', parse_url($url, PHP_URL_PATH))
    );
});

test('delete avatar removes file and clears profile avatar when valid and owned', function () {
    $profile = Profile::factory()->create([
        'user_id' => $this->user->id,
        'avatar' => 'http://localhost/storage/profiles/avatars/' . $this->user->id . '/avatar.jpg',
    ]);

    // Store the avatar file
    Storage::disk('public')->put(
        "profiles/avatars/{$this->user->id}/avatar.jpg",
        'dummy'
    );

    $response = $this->actingAs($this->user, 'sanctum')
        ->deleteJson('/api/profile/delete-avatar', [
            'url' => $profile->avatar,
        ]);

    $response->assertOk()
        ->assertJson(['message' => 'Avatar deleted successfully']);

    Storage::disk('public')->assertMissing("profiles/avatars/{$this->user->id}/avatar.jpg");
    $this->assertDatabaseHas('profiles', [
        'id' => $profile->id,
        'avatar' => null,
    ]);
});

test('delete avatar returns 403 if url does not match profile avatar', function () {
    $profile = Profile::factory()->create([
        'user_id' => $this->user->id,
        'avatar' => 'http://localhost/storage/profiles/avatars/' . $this->user->id . '/avatar.jpg',
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->deleteJson('/api/profile/delete-avatar', [
            'url' => 'http://localhost/storage/profiles/avatars/other-user/avatar.jpg',
        ]);

    $response->assertStatus(403);
});

test('destroy deletes profile and calls user deletion service', function () {
    $profile = Profile::factory()->create(['user_id' => $this->user->id]);

    $this->mockDeletesUserAccount
        ->shouldReceive('deleteAccount')
        ->once()
        ->with($this->user->id);

    $response = $this->actingAs($this->user, 'sanctum')
        ->deleteJson('/api/profile');

    $response->assertStatus(204);
    $this->assertDatabaseMissing('profiles', ['id' => $profile->id]);
});