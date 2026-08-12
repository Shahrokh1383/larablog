<?php

namespace Modules\Identity\Services\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface FetchesUsersByRole
{
    public function getPaginatedUsersWithRoles(array $roles, ?string $search, int $perPage, array $excludedIds = []): LengthAwarePaginator;
    
    /**
     * Fetches a map of users with their roles for given user IDs.
     * 
     * @param array $userIds
     * @return array Map of user_id => ['id', 'name', 'email', 'roles']
     */
    public function getUsersWithRolesMap(array $userIds): array;
}