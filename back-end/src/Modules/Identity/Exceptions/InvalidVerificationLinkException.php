<?php

namespace Modules\Identity\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InvalidVerificationLinkException extends HttpException
{
    public function __construct(string $message = 'Invalid or expired verification link.')
    {
        parent::__construct(403, $message);
    }
}