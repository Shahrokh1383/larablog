<?php

namespace Modules\Identity\Exceptions;

use Shared\Exceptions\DomainException;

class EmailAlreadyRegisteredException extends DomainException
{
    public function __construct(string $message = 'The email address is already registered.')
    {
        parent::__construct($message, 422);
    }
}