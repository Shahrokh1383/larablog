<?php

namespace Modules\Identity\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Modules\Identity\Actions\FindOrCreateSocialUserAction;
use Modules\Identity\DTOs\OAuthCallbackDTO;
use Modules\Identity\Models\User;
use Shared\Exceptions\DomainException;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OAuthService
{
    public function __construct(
        protected FindOrCreateSocialUserAction $findOrCreate,
    ) {}

    public function redirect(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);
        
        /** @var AbstractProvider $driver */
        $driver = Socialite::driver($provider);

        return $driver->stateless()->redirect();
    }

    public function callback(OAuthCallbackDTO $dto): User
    {
        $this->validateProvider($dto->provider);

        try {
            /** @var AbstractProvider $driver */
            $driver = Socialite::driver($dto->provider);
            $socialUser = $driver->stateless()->user();
        } catch (\Throwable $e) {
            Log::error('OAuth callback failed', [
                'provider'        => $dto->provider,
                'exception_class' => get_class($e),
                'message'         => $e->getMessage(),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
            ]);

            throw new DomainException('OAuth callback failed.', 422);
        }

        $user = $this->findOrCreate->execute($socialUser, $dto->provider);

        Auth::login($user);

        return $user;
    }

    protected function validateProvider(string $provider): void
    {
        $allowedProviders = array_keys(array_filter(config('services', []), function ($config) {
            return isset($config['client_id'], $config['client_secret'], $config['redirect']);
        }));

        if (! in_array($provider, $allowedProviders, true)) {
            throw new DomainException('Unsupported OAuth provider.', 422);
        }
    }
}