<?php

namespace Modules\Profile\Services;

use Illuminate\Support\Facades\DB;
use Modules\Identity\Services\Contracts\UpdatesUserBasicInfo;
use Modules\Profile\DTOs\UpdateProfileDTO;
use Modules\Profile\Models\Profile;
use Modules\Profile\Services\Contracts\ProfileServiceInterface;
use Modules\Profile\Services\Contracts\FetchesPublicProfiles;
use Shared\Models\User;

class ProfileService implements ProfileServiceInterface, FetchesPublicProfiles
{
    public function __construct(
        private UpdatesUserBasicInfo $identityService
    ) {}

    public function getByUserId(string $userId): ?Profile
    {
        return Profile::where('user_id', $userId)->first();
    }

    public function getPublicProfileByUsername(string $username): ?Profile
    {
        // Pragmatic DDD: Querying Shared\Models\User directly is legal and avoids over-engineering.
        $user = User::where('username', $username)->first();
        if (!$user) {
            return null;
        }

        return Profile::where('user_id', $user->id)->first();
    }

    public function updateProfile(string $userId, UpdateProfileDTO $dto): Profile
    {
        return DB::transaction(function () use ($userId, $dto) {
            // 1. Delegate name update to Identity module
            $this->identityService->updateName($userId, $dto->name);

            // 2. Update Profile data
            $profile = Profile::firstOrCreate(['user_id' => $userId]);
            
            $profile->update([
                'avatar'              => $dto->avatar,
                'bio'                 => $dto->bio,
                'expertise'           => $dto->expertise,
                'years_of_experience' => $dto->years_of_experience,
                'social_links'        => $dto->social_links,
            ]);

            return $profile->fresh();
        });
    }

    public function getPublicProfilesMap(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        // Eager load the shared User model to avoid N+1 queries
        $profiles = Profile::with('user')->whereIn('user_id', $userIds)->get();

        return $profiles->mapWithKeys(function (Profile $profile) {
            return [
                $profile->user_id => [
                'name'     => $profile->user->name,
                    'username' => $profile->user->username,
                    'avatar'   => $profile->avatar,
                    'bio'      => $profile->bio,
                ]
            ];
        })->all();
    }
}