<?php

namespace Modules\Identity\Actions;

use Modules\Identity\Models\User;
use Modules\Identity\DTOs\UserRegisterDTO;
use Illuminate\Support\Facades\Hash;

class CreateUserAction
{
    public function execute(UserRegisterDTO $dto): User
    {
        return User::create([
            'name'     => $dto->name,
            'email'    => $dto->email,
            'password' => Hash::make($dto->password),
        ]);
    }
}