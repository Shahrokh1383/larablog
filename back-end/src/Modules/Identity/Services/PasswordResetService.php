<?php

namespace Modules\Identity\Services;

use Illuminate\Support\Facades\Password;
use Modules\Identity\DTOs\ForgotPasswordDTO;
use Modules\Identity\DTOs\ResetPasswordDTO;
use Modules\Identity\Exceptions\PasswordResetFailedException;

class PasswordResetService
{
    public function sendResetLink(ForgotPasswordDTO $dto): string
    {
        $status = Password::sendResetLink(['email' => $dto->email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw new PasswordResetFailedException(__($status));
        }

        return __($status);
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
            $user->tokens()->delete(); // Revoke all existing tokens
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw new PasswordResetFailedException(__($status));
        }

        return __($status);
    }
}