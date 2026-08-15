<?php

namespace Shared\Contracts;

interface HasRolesContract
{
    /**
     * Check if the user has any of the given roles.
     *
     * @param array<string>|string $roles
     */
    public function hasAnyRole(array|string $roles): bool;

    /**
     * Check if the user has a specific role.
     *
     * @param string $role
     */
    public function hasRole(string $role): bool;
}