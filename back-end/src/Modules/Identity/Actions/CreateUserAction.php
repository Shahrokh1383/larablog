<?php

namespace Modules\Identity\Actions;

use Modules\Identity\Models\User;
use Modules\Identity\DTOs\UserRegisterDTO;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateUserAction
{
    public function execute(UserRegisterDTO $dto): User
    {
        return User::create([
            'name'     => $dto->name,
            'username' => $this->generateUniqueUsername($dto->email),
            'email'    => $dto->email,
            'password' => Hash::make($dto->password),
        ]);
    }

    private function generateUniqueUsername(string $email): string
    {
        $baseUsername = Str::before($email, '@');
        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }
}