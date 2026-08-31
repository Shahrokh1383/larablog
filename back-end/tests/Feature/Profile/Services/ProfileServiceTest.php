<?php

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Identity\Services\Contracts\DeletesUserAccount;
use Modules\Identity\Services\Contracts\UpdatesUserBasicInfo;
use Modules\Profile\Models\Profile;
use Modules\Profile\Services\ProfileService;
use Shared\Models\User;

beforeEach(function () {
    $this->mockUpdatesUserBasicInfo = Mockery::mock(UpdatesUserBasicInfo::class);
    $this->mockDeletesUserAccount = Mockery::mock(DeletesUserAccount::class);

    $this->service = new ProfileService(
        $this->mockUpdatesUserBasicInfo,
        $this->mockDeletesUserAccount
    );
});

test('getByUserId returns profile or null', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->create(['user_id' => $user->id]);

    expect($this->service->getByUserId($user->id)->id)->toBe($profile->id);
    expect($this->service->getByUserId('non-existent'))->toBeNull();
});

test('ensureProfileExists creates profile if missing', function () {
    $user = User::factory()->create();
    expect(Profile::where('user_id', $user->id)->exists())->toBeFalse();

    $profile = $this->service->ensureProfileExists($user->id);
    expect($profile)->toBeInstanceOf(Profile::class);
    expect(Profile::where('user_id', $user->id)->exists())->toBeTrue();

    // Should return existing on second call
    $second = $this->service->ensureProfileExists($user->id);
    expect($second->id)->toBe($profile->id);
});

test('getPublicProfileByUsername returns profile with user eager loaded', function () {
    $user = User::factory()->create(['username' => 'johndoe']);
    $profile = Profile::factory()->create(['user_id' => $user->id]);

    $found = $this->service->getPublicProfileByUsername('johndoe');
    expect($found->id)->toBe($profile->id);
    expect($found->relationLoaded('user'))->toBeTrue();
    expect($found->user->id)->toBe($user->id);

    expect($this->service->getPublicProfileByUsername('nouser'))->toBeNull();
});

test('updateProfile updates name via identity service and profile fields', function () {
    $user = User::factory()->create(['name' => 'Old Name']);
    $profile = Profile::factory()->create(['user_id' => $user->id, 'bio' => 'Old bio']);

    $this->mockUpdatesUserBasicInfo
        ->shouldReceive('updateName')
        ->once()
        ->with(Mockery::on(fn ($argUser) => $argUser->id === $user->id), 'New Name');

    $updated = $this->service->updateProfile($user->id, [
        'name' => 'New Name',
        'bio' => 'Updated bio',
    ]);

    expect($updated->bio)->toBe('Updated bio');
    expect($updated->user->name)->toBe('Old Name'); // name updated via identity service, not in profile
    $this->assertDatabaseHas('profiles', [
        'id' => $profile->id,
        'bio' => 'Updated bio',
    ]);
});

test('updateProfile without name does not call identity service', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->create(['user_id' => $user->id]);

    $this->mockUpdatesUserBasicInfo->shouldNotReceive('updateName');

    $updated = $this->service->updateProfile($user->id, [
        'expertise' => 'PHP',
    ]);

    expect($updated->expertise)->toBe('PHP');
});

test('getPublicProfilesMap returns map keyed by user id', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $profile1 = Profile::factory()->create(['user_id' => $user1->id, 'bio' => 'Bio1']);
    $profile2 = Profile::factory()->create(['user_id' => $user2->id, 'bio' => 'Bio2']);

    $map = $this->service->getPublicProfilesMap([$user1->id, $user2->id]);

    expect($map)->toHaveCount(2);
    expect($map[$user1->id]['bio'])->toBe('Bio1');
    expect($map[$user2->id]['bio'])->toBe('Bio2');
    expect($map[$user1->id])->toHaveKeys(['id', 'name', 'username', 'avatar', 'bio', 'expertise', 'social_links']);

    // Empty input
    expect($this->service->getPublicProfilesMap([]))->toBe([]);
});

test('getAllPublicProfiles returns paginated profiles with non-null bio and optional search', function () {
    $user1 = User::factory()->create(['name' => 'Alice']);
    $user2 = User::factory()->create(['name' => 'Bob']);
    $user3 = User::factory()->create(['name' => 'Charlie']);

    Profile::factory()->create(['user_id' => $user1->id, 'bio' => 'About Alice', 'expertise' => 'PHP']);
    Profile::factory()->create(['user_id' => $user2->id, 'bio' => 'About Bob', 'expertise' => 'JS']);
    Profile::factory()->create(['user_id' => $user3->id, 'bio' => null]); // should be excluded

    $paginator = $this->service->getAllPublicProfiles(null, 10);
    expect($paginator)->toBeInstanceOf(LengthAwarePaginator::class);
    expect($paginator->total())->toBe(2);

    $searchPaginator = $this->service->getAllPublicProfiles('PHP', 10);
    expect($searchPaginator->total())->toBe(1);
    expect($searchPaginator->first()->user->name)->toBe('Alice');
});

test('deleteAccount deletes profile and calls user deletion service', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->create(['user_id' => $user->id]);

    $this->mockDeletesUserAccount
        ->shouldReceive('deleteAccount')
        ->once()
        ->with($user->id);

    $this->service->deleteAccount($user->id);

    $this->assertDatabaseMissing('profiles', ['id' => $profile->id]);
});