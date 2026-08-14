<?php

namespace Modules\Identity\Actions;

use Illuminate\Database\QueryException;
use Modules\Identity\DTOs\UserRegisterDTO;
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
            } catch (QueryException $e) {
                if ($attempt >= 9 || ! $this->isDuplicateUsernameEntry($e)) {
                    throw $e;
                }
                $attempt++;
            }
        } while (true);
    }

    private function isDuplicateUsernameEntry(QueryException $e): bool
    {
        if (($e->errorInfo[0] ?? '') !== '23000') {
            return false;
        }

        $message = $e->getMessage();

        return str_contains($message, 'users_username_unique')
            || str_contains($message, 'users_username');
    }
}