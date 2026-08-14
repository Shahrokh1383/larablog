<?php

namespace Modules\Identity\Actions;

use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Modules\Identity\Models\User;

class FindOrCreateSocialUserAction
{
    public function __construct(
        protected GenerateUniqueUsernameAction $generateUsername,
    ) {}

    public function execute(SocialiteUser $socialUser, string $provider): User
    {
        $user = User::where('email', $socialUser->getEmail())->first();

        if (! $user) {
            $name = $socialUser->getName()
                ?? $socialUser->getNickname()
                ?? $socialUser->getEmail();

            $attempt = 0;

            do {
                $username = $this->generateUsername->execute($socialUser->getEmail(), $attempt);

                try {
                    $user = User::create([
                        'name'     => $name,
                        'username' => $username,
                        'email'    => $socialUser->getEmail(),
                        'password' => Str::random(32),
                    ]);
                    break;
                } catch (QueryException $e) {
                    if ($attempt >= 9 || ! $this->isDuplicateEntry($e)) {
                        throw $e;
                    }
                    $attempt++;
                }
            } while (true);

            $user->assignRole('user');
            $user->markEmailAsVerified();
        }

        return $user;
    }

    private function isDuplicateEntry(QueryException $e): bool
    {
        $code = $e->errorInfo[1] ?? null;
        return in_array($code, [1062, 23505, 19], true);
    }
}