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
            $user = User::create([
                'name'     => $socialUser->getName(),
                'email'    => $socialUser->getEmail(),
                'password' => bcrypt(\Illuminate\Support\Str::random(32)),
            ]);
            $user->assignRole('user');
        }

        // Optionally store provider_id in a dedicated table; skipping for brevity.

        return $user;
    }
}