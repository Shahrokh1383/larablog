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
        $code = $e->errorInfo[1] ?? null;

        // MySQL: 1062, PostgreSQL: 23505, SQLite: 19
        return in_array($code, [1062, 23505, 19], true);
    }
}