<?php

namespace Modules\Identity\Services\Contracts;

interface UpdatesUserBasicInfo
{
    public function updateName(string $userId, string $name): void;
}