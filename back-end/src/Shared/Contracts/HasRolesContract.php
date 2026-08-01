<?php

namespace Shared\Contracts;

interface HasRolesContract
{
    public function hasRole(string $role): bool;
    public function hasAnyRole(array $roles): bool;
}