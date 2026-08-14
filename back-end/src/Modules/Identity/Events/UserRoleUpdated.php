<?php

namespace Modules\Identity\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Shared\Models\User;

class UserRoleUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public string $role,
    ) {}
}