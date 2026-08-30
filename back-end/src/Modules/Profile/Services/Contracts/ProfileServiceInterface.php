<?php

namespace Modules\Profile\Services\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Profile\DTOs\UpdateProfileDTO;
use Modules\Profile\Models\Profile;

interface ProfileServiceInterface
{
    public function getByUserId(string $userId): ?Profile;
    public function ensureProfileExists(string $userId): Profile;
    public function getPublicProfileByUsername(string $username): ?Profile;
    public function updateProfile(string $userId, UpdateProfileDTO $dto): Profile;
    public function getPublicProfilesMap(array $userIds): array;
    public function getAllPublicProfiles(?string $search = null, int $perPage = 12): LengthAwarePaginator;
    public function getPublicProfilesWithStats(?string $search, int $perPage): LengthAwarePaginator;
    public function getPublicProfileWithStats(string $username): ?Profile;
    public function deleteAccount(string $userId): void;
}