<?php

namespace Modules\Identity\Services\Contracts;

use Illuminate\Support\Collection;

interface FetchesUsersByRole
{
    /**
     * Get users that have at least one of the given roles.
     *
     * @param array $roles e.g. ['admin', 'editor', 'author']
     * @return Collection of User models
     */
    public function getUsersWithRoles(array $roles): Collection;
}