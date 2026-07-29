<?php

use Tests\Support\SmtpSinkService;

pest()->extend(Tests\TestCase::class)
    ->in('Feature');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/**
 * Returns true if the SMTP sink server is reachable.
 */
function smtpReachable(): bool
{
    try {
        (new SmtpSinkService)->getAllEmails();
        return true;
    } catch (\Exception $e) {
        return false;
    }
}