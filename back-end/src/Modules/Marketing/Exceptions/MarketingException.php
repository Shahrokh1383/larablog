<?php

namespace Modules\Marketing\Exceptions;

use Shared\Exceptions\DomainException;

class MarketingException extends DomainException
{
    public static function alreadySubscribed(): self
    {
        return new self('This email is already subscribed to our newsletter.', 409);
    }

    public static function alreadyReplied(): self
    {
        return new self('This contact message has already been replied to.', 409);
    }

    public static function subscriberSelectionRequired(): self
    {
        return new self('At least one subscriber must be selected when send_to_all is false.', 422);
    }
}