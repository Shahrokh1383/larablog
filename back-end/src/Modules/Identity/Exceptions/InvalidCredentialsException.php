<?php

namespace Modules\Identity\Exceptions;

use Shared\Exceptions\DomainException;

class InvalidCredentialsException extends DomainException
{
    public function __construct(string $message = 'The provided credentials are incorrect.')
    {
        parent::__construct($message, 422);
    }
}