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
        $baseUsername = Str::lower(Str::before($email, '@'));
        $baseUsername = preg_replace('/[^a-z0-9]/', '', $baseUsername);

        if ($baseUsername === '') {
            $baseUsername = 'user';
        }

        return $attempt === 0 ? $baseUsername : $baseUsername . $attempt;
    }
}