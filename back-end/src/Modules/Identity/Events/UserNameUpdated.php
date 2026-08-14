<?php

namespace Modules\Identity\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Shared\Models\User;

class UserNameUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public User $user) {}
}