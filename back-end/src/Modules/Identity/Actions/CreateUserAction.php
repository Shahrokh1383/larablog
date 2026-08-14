<?php

namespace Modules\Identity\Actions;

use Modules\Identity\DTOs\UserRegisterDTO;
use Modules\Identity\Models\User;

class CreateUserAction
{
    public function __construct(
        protected CreateUserWithUniqueUsernameAction $createUser,
    ) {}

    public function execute(UserRegisterDTO $dto): User
    {
        return $this->createUser->execute([
            'name'     => $dto->name,
            'email'    => $dto->email,
            'password' => $dto->password,
        ]);
    }
}