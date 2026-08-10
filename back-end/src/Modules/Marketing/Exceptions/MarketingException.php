<?php

namespace Modules\Marketing\Exceptions;

use Exception;

class MarketingException extends Exception
{
    public static function alreadySubscribed(): self
    {
        return new self('This email is already subscribed to our newsletter.', 409);
    }

    public static function noSubscribersFound(): self
    {
        return new self('No active subscribers found to send the newsletter.', 404);
    }
}