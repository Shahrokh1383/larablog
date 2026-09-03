<?php

namespace Modules\About\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

final class StoryImageUploadFailedException extends HttpException
{
    private const HTTP_STATUS = 500;

    public function __construct(string $message = 'The story image could not be uploaded.')
    {
        parent::__construct(self::HTTP_STATUS, $message);
    }
}