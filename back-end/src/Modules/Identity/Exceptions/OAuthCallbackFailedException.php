<?php

namespace Modules\Identity\Exceptions;

use Shared\Exceptions\DomainException;

class OAuthCallbackFailedException extends DomainException
{
    public function __construct(string $message = 'OAuth callback failed.')
    {
        parent::__construct($message, 422);
    }
}