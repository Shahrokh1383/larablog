<?php

namespace Modules\Profile\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\AdminStats\Services\Contracts\ContentStatsContract;
use Modules\Identity\Services\Contracts\DeletesUserAccount;
use Modules\Identity\Services\Contracts\UpdatesUserBasicInfo;
use Modules\Profile\DTOs\UpdateProfileDTO;
use Modules\Profile\Models\Profile;
use Modules\Profile\Services\Contracts\FetchesPublicProfiles;
use Modules\Profile\Services\Contracts\ProfileServiceInterface;
use Shared\Models\User;

class ProfileService implements ProfileServiceInterface, FetchesPublicProfiles
{
    public function __construct(
        private UpdatesUserBasicInfo $identityService,
        private DeletesUserAccount $userDeletionService,
        private ContentStatsContract $contentStatsService,
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
        $user = User::where('username', $username)->first();
        if (!$user) {
            return null;
        }

        return Profile::where('user_id', $user->id)->first();
    }

    public function updateProfile(string $userId, UpdateProfileDTO $dto): Profile
    {
        return DB::transaction(function () use ($userId, $dto) {
            $user = User::findOrFail($userId);
            $this->identityService->updateName($user, $dto->name);

            $profile = $this->ensureProfileExists($userId);

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

    public function getPublicProfilesWithStats(?string $search, int $perPage): LengthAwarePaginator
    {
        $profiles = $this->getAllPublicProfiles($search, $perPage);
        $stats = $this->contentStatsService->getAuthorStats();

        $profiles->through(function ($profile) use ($stats) {
            $userId = $profile->user_id;
            $profile->posts_count = $stats[$userId]['posts_count'] ?? 0;
            $profile->total_views = $stats[$userId]['total_views'] ?? 0;
            return $profile;
        });

        return $profiles;
    }

    public function getPublicProfileWithStats(string $username): ?Profile
    {
        $profile = $this->getPublicProfileByUsername($username);
        
        if (!$profile) {
            return null;
        }

        $stats = $this->contentStatsService->getAuthorStats();
        $userId = $profile->user_id;
        $profile->posts_count = $stats[$userId]['posts_count'] ?? 0;
        $profile->total_views = $stats[$userId]['total_views'] ?? 0;

        return $profile;
    }

    public function deleteAccount(string $userId): void
    {
        DB::transaction(function () use ($userId) {
            Profile::where('user_id', $userId)->delete();
            $this->userDeletionService->deleteAccount($userId);
        });
    }
}