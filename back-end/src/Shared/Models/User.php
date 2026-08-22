<?php

namespace Shared\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Shared\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Database\Factories\Modules\Identity\UserFactory;
use Spatie\Permission\Traits\HasRoles;
use Shared\Contracts\HasRolesContract;

class User extends Authenticatable implements HasRolesContract
{
    use HasUuid, HasFactory, Notifiable, HasRoles;

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