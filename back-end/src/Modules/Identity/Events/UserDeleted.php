<?php

namespace Modules\Identity\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Identity\Models\User;

class UserDeleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public User $user) {}
}