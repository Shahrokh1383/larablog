<?php

namespace Modules\Marketing\Exceptions;

use Shared\Exceptions\DomainException;

class MarketingException extends DomainException
{
    public static function alreadySubscribed(): self
    {
        return new self('This email is already subscribed to our newsletter.', 409);
    }

    public static function noSubscribersFound(): self
    {
        return new self('No active subscribers found to send the newsletter.', 404);
    }

    public static function subscriberSelectionRequired(): self
    {
        return new self('At least one subscriber must be selected when send_to_all is false.', 422);
    }
}