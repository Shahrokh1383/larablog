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
                // Definitively check if the email already exists.
                // This avoids fragile string parsing of database error messages.
                if (User::where('email', $dto->email)->exists()) {
                    throw new EmailAlreadyRegisteredException();
                }

                // If the email is not the cause, it must be the username constraint.
                // Retry with a new generated username.
                if ($attempt >= 9) {
                    throw new UsernameGenerationFailedException();
                }

                $attempt++;
            }
        } while (true);
    }
}