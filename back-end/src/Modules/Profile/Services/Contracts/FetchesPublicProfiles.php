<?php

namespace Modules\Profile\Services\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Profile\Models\Profile;

interface FetchesPublicProfiles
{
    /**
     * Get a paginated list of public profiles for the directory.
     */
    public function getAllPublicProfiles(?string $search = null, int $perPage = 12): LengthAwarePaginator;

    /**
     * Get a single public profile by username with eager-loaded user data.
     */
    public function getPublicProfileByUsername(string $username): ?Profile;

    /**
     * Get a map of profiles indexed by user ID (useful for cross-module aggregation).
     * 
     * @param array<string> $userIds
     * @return array<string, array<string, mixed>>
     */
    public function getPublicProfilesMap(array $userIds): array;
}