<?php

namespace Modules\Identity\Actions;

use Modules\Identity\Models\User;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class FindOrCreateSocialUserAction
{
    public function execute(SocialiteUser $socialUser, string $provider): User
    {
        $user = User::where('email', $socialUser->getEmail())->first();

        if (! $user) {
            // Fallback to nickname or email if name is null (common in GitHub)
            $name = $socialUser->getName() ?? $socialUser->getNickname() ?? $socialUser->getEmail();

            $user = User::create([
                'name'     => $name,
                'email'    => $socialUser->getEmail(),
                'password' => bcrypt(\Illuminate\Support\Str::random(32)),
            ]);
            
            $user->assignRole('user');
            
            // Social providers already verified the email
            $user->markEmailAsVerified();
        }

        return $user;
    }
}