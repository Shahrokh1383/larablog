<?php

namespace Modules\Identity\Services\Contracts;

use Shared\Models\User;

interface UpdatesUserBasicInfo
{
    public function updateName(User $user, string $name): void;
}