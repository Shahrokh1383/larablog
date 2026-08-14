<?php

namespace Modules\Identity\Actions;

use Illuminate\Support\Str;

class GenerateUniqueUsernameAction
{
    /**
     * Generate a username from an email address.
     *
     * @param string $email
     * @param int $attempt 0 = base, >0 = base . attempt
     */
    public function execute(string $email, int $attempt = 0): string
    {
        $baseUsername = Str::before($email, '@');

        if ($attempt === 0) {
            return $baseUsername;
        }

        return $baseUsername . $attempt;
    }
}