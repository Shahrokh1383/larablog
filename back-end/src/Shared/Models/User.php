<?php

namespace Shared\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Shared\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\Modules\Identity\UserFactory;

class User extends Authenticatable
{
    use HasUuid, HasFactory;

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * Define the custom broadcast channel name for notifications.
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        return 'users.' . $this->id;
    }
}