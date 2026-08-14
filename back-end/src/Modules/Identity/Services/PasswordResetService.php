<?php

namespace Modules\Identity\Services;

use Modules\Identity\DTOs\ForgotPasswordDTO;
use Modules\Identity\DTOs\ResetPasswordDTO;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    public function sendResetLink(ForgotPasswordDTO $dto): string
    {
        $status = Password::sendResetLink(['email' => $dto->email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
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
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return __($status);
    }
}