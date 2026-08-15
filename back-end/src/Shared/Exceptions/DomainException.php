<?php

namespace Shared\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class DomainException extends HttpException
{
    public function __construct(string $message, int $statusCode = 422)
    {
        parent::__construct($statusCode, $message);
    }
}