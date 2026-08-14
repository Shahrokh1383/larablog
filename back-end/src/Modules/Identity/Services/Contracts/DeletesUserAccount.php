<?php

namespace Modules\Identity\Services\Contracts;

interface DeletesUserAccount
{
    /**
     * Delete a user account by its unique identifier.
     *
     * This is the only legal way for other bounded contexts to request
     * user deletion without reaching into Identity internals.
     */
    public function deleteAccount(string $userId): void;
}