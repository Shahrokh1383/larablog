<?php

namespace Modules\Identity\Exceptions;

use Shared\Exceptions\DomainException;

class UnsupportedOAuthProviderException extends DomainException
{
    public function __construct(string $message = 'Unsupported OAuth provider.')
    {
        parent::__construct($message, 422);
    }
}