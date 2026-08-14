<?php

namespace Modules\Identity\Exceptions;

use Shared\Exceptions\DomainException;

class PasswordResetFailedException extends DomainException
{
    public function __construct(string $message = 'Password reset failed.')
    {
        parent::__construct($message, 422);
    }
}