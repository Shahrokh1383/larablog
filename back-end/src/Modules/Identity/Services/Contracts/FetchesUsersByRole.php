<?php

namespace Modules\Identity\Services\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface FetchesUsersByRole
{
    public function getPaginatedUsersWithRoles(array $roles, ?string $search, int $perPage): LengthAwarePaginator;
}