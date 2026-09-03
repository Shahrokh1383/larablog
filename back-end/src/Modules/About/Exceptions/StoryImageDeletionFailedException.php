<?php

namespace Modules\About\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

final class StoryImageDeletionFailedException extends HttpException
{
    private const HTTP_STATUS = 422;

    public function __construct(string $message = 'The story image could not be deleted.')
    {
        parent::__construct(self::HTTP_STATUS, $message);
    }
}