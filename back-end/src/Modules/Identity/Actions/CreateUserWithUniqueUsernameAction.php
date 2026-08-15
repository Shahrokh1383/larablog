<?php

namespace Modules\Identity\Actions;

use Illuminate\Database\UniqueConstraintViolationException;
use Modules\Identity\DTOs\UserRegisterDTO;
use Modules\Identity\Exceptions\EmailAlreadyRegisteredException;
use Modules\Identity\Exceptions\UsernameGenerationFailedException;
use Modules\Identity\Models\User;

class CreateUserWithUniqueUsernameAction
{
    public function __construct(
        protected GenerateUniqueUsernameAction $generateUsername,
    ) {}

    public function execute(UserRegisterDTO $dto): User
    {
        $attempt = 0;

        do {
            $username = $this->generateUsername->execute($dto->email, $attempt);

            try {
                return User::create([
                    'name'     => $dto->name,
                    'email'    => $dto->email,
                    'password' => $dto->password,
                    'username' => $username,
                ]);
            } catch (UniqueConstraintViolationException $e) {
                if ($this->isUsernameConstraint($e)) {
                    if ($attempt >= 9) {
                        throw new UsernameGenerationFailedException();
                    }
                    $attempt++;
                    continue;
                }

                if ($this->isEmailConstraint($e)) {
                    throw new EmailAlreadyRegisteredException();
                }

                // Unknown unique constraint – rethrow
                throw $e;
            }
        } while (true);
    }

    private function isUsernameConstraint(UniqueConstraintViolationException $e): bool
    {
        $message = $e->getMessage();

        return str_contains($message, 'users_username_unique')
            || str_contains($message, 'users_username');
    }

    private function isEmailConstraint(UniqueConstraintViolationException $e): bool
    {
        $message = $e->getMessage();

        return str_contains($message, 'users_email_unique')
            || str_contains($message, 'users_email');
    }
}