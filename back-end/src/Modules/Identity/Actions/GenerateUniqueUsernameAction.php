<?php

namespace Modules\Identity\Actions;

use Illuminate\Support\Str;
use Modules\Identity\Models\User;

class GenerateUniqueUsernameAction
{
    public function execute(string $email): string
    {
        $baseUsername = Str::before($email, '@');
        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }
}