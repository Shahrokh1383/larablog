<?php

namespace Shared\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Shared\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\Modules\Identity\UserFactory;

class User extends Authenticatable
{
    use HasUuid, HasFactory;

    /**
     * Use the Identity module factory (produces Modules\Identity\Models\User)
     * which extends this Shared user. This keeps boundaries clean.
     */
    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}