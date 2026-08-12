<?php

namespace Modules\Profile\Services;

use Illuminate\Support\Facades\DB;
use Modules\Identity\Services\Contracts\UpdatesUserBasicInfo;
use Modules\Profile\DTOs\UpdateProfileDTO;
use Modules\Profile\Models\Profile;
use Modules\Profile\Services\Contracts\ProfileServiceInterface;
use Modules\Profile\Services\Contracts\FetchesPublicProfiles;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Shared\Models\User;

class ProfileService implements ProfileServiceInterface, FetchesPublicProfiles
{
    public function __construct(
        private UpdatesUserBasicInfo $identityService
    ) {}

    public function getByUserId(string $userId): ?Profile
    {
        return Profile::firstOrCreate(['user_id' => $userId]);
    }

    public function getPublicProfileByUsername(string $username): ?Profile
    {
        $user = User::where('username', $username)->first();
        if (!$user) {
            return null;
        }

        return Profile::where('user_id', $user->id)->first();
    }

    public function updateProfile(string $userId, UpdateProfileDTO $dto): Profile
    {
        return DB::transaction(function () use ($userId, $dto) {
            $this->identityService->updateName($userId, $dto->name);

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

        $profiles = Profile::whereIn('user_id', $userIds)->get();

        return $profiles->mapWithKeys(function (Profile $profile) {
            return [
                $profile->user_id => [
                    'avatar'       => $profile->avatar,
                    'bio'          => $profile->bio,
                    'expertise'    => $profile->expertise,
                    'social_links' => $profile->social_links ?? [],
                ]
            ];
        })->all();
    }

    public function getAllPublicProfiles(?string $search = null, int $perPage = 12): LengthAwarePaginator
    {
        return Profile::with('user')
            ->whereNotNull('bio')
            ->when($search, function ($query) use ($search) {
                $query->where('expertise', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                      });
            })
            ->paginate($perPage);
    }

    public function deleteAccount(string $userId): void
    {
        DB::transaction(function () use ($userId) {
            Profile::where('user_id', $userId)->delete();
            User::where('id', $userId)->delete();
        });
    }
}