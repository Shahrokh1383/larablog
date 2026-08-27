<?php

namespace Tests\Feature\Articles\Policies\Support;

use Shared\Contracts\HasRolesContract;
use Mockery;
use Mockery\MockInterface;
use Illuminate\Support\Str;

final class UserRoleMockFactory
{
    public static function make(array $roles, ?string $id = null): HasRolesContract
    {
        $mock = Mockery::mock(HasRolesContract::class, function (MockInterface $mock) use ($roles) {
            $mock->shouldReceive('hasAnyRole')
                ->andReturnUsing(fn ($checkRoles) => !empty(array_intersect((array) $checkRoles, $roles)));

            $mock->shouldReceive('hasRole')
                ->andReturnUsing(fn (string $role) => in_array($role, $roles));
        });

        $mock->id = $id ?? (string) Str::uuid();

        return $mock;
    }
}