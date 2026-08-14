<?php

namespace Modules\Identity\Exceptions;

use Shared\Exceptions\DomainException;

class AdminAccessDeniedException extends DomainException
{
    public function __construct(string $message = 'You do not have permission to access the admin panel.')
    {
        parent::__construct($message, 403);
    }
}