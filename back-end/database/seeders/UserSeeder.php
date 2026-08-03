<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Identity\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure the 'user' role exists (RoleSeeder already ran, but safe)
        $role = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Create 200 users and assign the 'user' role
        User::factory()
            ->count(200)
            ->create()
            ->each(fn (User $user) => $user->assignRole($role));
    }
}