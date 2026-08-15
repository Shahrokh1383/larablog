<?php

namespace Modules\Identity\Exceptions;

use Shared\Exceptions\DomainException;

class UsernameGenerationFailedException extends DomainException
{
    public function __construct(string $message = 'Could not generate a unique username. Please try again.')
    {
        parent::__construct($message, 422);
    }
}