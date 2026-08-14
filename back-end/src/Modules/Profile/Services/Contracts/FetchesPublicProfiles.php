<?php

namespace Modules\Profile\Services\Contracts;

interface FetchesPublicProfiles
{
    /**
     * Fetches public profile data for a given list of user IDs.
     * 
     * @param array<string|int> $userIds
     * @return array<string|int, array> Map of user_id => ['id', 'name', 'username', 'avatar', 'bio', 'expertise', 'social_links']
     */
    public function getPublicProfilesMap(array $userIds): array;
}