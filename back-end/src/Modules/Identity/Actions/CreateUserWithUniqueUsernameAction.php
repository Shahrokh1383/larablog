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
                if ($attempt >= 9 || ! $this->isDuplicateEntry($e)) {
                    throw $e;
                }
                $attempt++;
            }
        } while (true);
    }

    private function isDuplicateEntry(QueryException $e): bool
    {
        // SQLSTATE 23000 = integrity constraint violation (unique, primary key, etc.)
        return ($e->errorInfo[0] ?? '') === '23000';
    }
}