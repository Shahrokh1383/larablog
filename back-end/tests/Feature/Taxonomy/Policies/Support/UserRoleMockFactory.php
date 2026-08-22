<?php

namespace Tests\Feature\Taxonomy\Policies\Support;

use Shared\Contracts\HasRolesContract;
use Mockery;
use Mockery\MockInterface;

final class UserRoleMockFactory
{
    public static function make(array $roles): HasRolesContract
    {
        return Mockery::mock(HasRolesContract::class, function (MockInterface $mock) use ($roles) {
            $mock->shouldReceive('hasAnyRole')
                ->andReturnUsing(fn ($checkRoles) => !empty(array_intersect((array) $checkRoles, $roles)));

            $mock->shouldReceive('hasRole')
                ->andReturnUsing(fn (string $role) => in_array($role, $roles));
        });
    }
}