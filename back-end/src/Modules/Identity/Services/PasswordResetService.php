<?php

namespace Modules\Identity\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Modules\Identity\Actions\RevokeUserSessionsAction;
use Modules\Identity\DTOs\ResetPasswordDTO;
use Modules\Identity\Events\UserPasswordUpdated;
use Modules\Identity\Exceptions\PasswordResetFailedException;

class PasswordResetService
{
    public function __construct(
        protected RevokeUserSessionsAction $revokeSessions,
    ) {}

    public function sendResetLink(string $email): string
    {
        Password::sendResetLink(['email' => $email]);

        return 'If the email address is registered, a password reset link has been sent.';
    }

    public function reset(ResetPasswordDTO $dto): string
    {
        $status = Password::reset([
            'token'                 => $dto->token,
            'email'                 => $dto->email,
            'password'              => $dto->password,
            'password_confirmation' => $dto->password,
        ], function ($user, $password) {
            $user->password = $password;
            $user->save();
            $user->tokens()->delete();
            $this->revokeSessions->execute($user);

            DB::afterCommit(function () use ($user) {
                event(new UserPasswordUpdated($user));
            });
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw new PasswordResetFailedException(__($status));
        }

        return __($status);
    }
}