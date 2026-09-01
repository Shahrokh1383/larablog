<?php

namespace Modules\Profile\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Identity\Services\Contracts\DeletesUserAccount;
use Modules\Identity\Services\Contracts\UpdatesUserBasicInfo;
use Modules\Profile\Models\Profile;
use Modules\Profile\Services\Contracts\FetchesPublicProfiles;
use Modules\Profile\Services\Contracts\ProfileServiceInterface;
use Shared\Models\User;

class ProfileService implements ProfileServiceInterface, FetchesPublicProfiles
{
    public function __construct(
        private UpdatesUserBasicInfo $identityService,
        private DeletesUserAccount $userDeletionService,
    ) {}

    public function getByUserId(string $userId): ?Profile
    {
        return Profile::where('user_id', $userId)->first();
    }

    public function ensureProfileExists(string $userId): Profile
    {
        return Profile::firstOrCreate(['user_id' => $userId]);
    }

    public function getPublicProfileByUsername(string $username): ?Profile
    {
        // Fix N+1: Eager load user relationship
        return Profile::with('user')->whereHas('user', function ($q) use ($username) {
            $q->where('username', $username);
        })->first();
    }

    public function updateProfile(string $userId, array $data): Profile
    {
        return DB::transaction(function () use ($userId, $data) {
            $user = User::findOrFail($userId);
            
            // Handle name update separately via Identity bounded context
            if (array_key_exists('name', $data)) {
                $this->identityService->updateName($user, $data['name']);
            }

            $profile = $this->ensureProfileExists($userId);
            
            // Filter out 'name' as it's handled above, update only provided fields
            $profileData = collect($data)->except('name')->all();
            
            if (!empty($profileData)) {
                $profile->update($profileData);
            }

            return $profile->fresh();
        });
    }

    public function getPublicProfilesMap(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        return Profile::with('user')
            ->whereIn('user_id', $userIds)
            ->get()
            ->mapWithKeys(function (Profile $profile) {
                return [
                    $profile->user_id => [
                        'id'           => $profile->user_id,
                        'name'         => $profile->user->name ?? null,
                        'username'     => $profile->user->username ?? null,
                        'avatar'       => $profile->avatar,
                        'bio'          => $profile->bio,
                        'expertise'    => $profile->expertise,
                        'social_links' => $profile->social_links ?? [],
                    ]
                ];
            })
            ->all();
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
            $this->userDeletionService->deleteAccount($userId);
        });
    }
}