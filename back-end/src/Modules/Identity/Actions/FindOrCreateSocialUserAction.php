<?php

namespace Modules\Identity\Actions;

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

            $user = User::create([
                'name'     => $name,
                'username' => $this->generateUsername->execute($socialUser->getEmail()),
                'email'    => $socialUser->getEmail(),
                'password' => bcrypt(Str::random(32)),
            ]);

            $user->assignRole('user');
            $user->markEmailAsVerified();
        }

        return $user;
    }
}