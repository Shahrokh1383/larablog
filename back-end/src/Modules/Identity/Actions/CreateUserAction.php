<?php

namespace Modules\Identity\Actions;

use Illuminate\Support\Facades\Hash;
use Modules\Identity\DTOs\UserRegisterDTO;
use Modules\Identity\Models\User;

class CreateUserAction
{
    public function __construct(
        protected GenerateUniqueUsernameAction $generateUsername,
    ) {}

    public function execute(UserRegisterDTO $dto): User
    {
        return User::create([
            'name'     => $dto->name,
            'username' => $this->generateUsername->execute($dto->email),
            'email'    => $dto->email,
            'password' => Hash::make($dto->password),
        ]);
    }
}