<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Identity\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        User::factory()->count(200)->create()
            ->each(fn (User $user) => $user->assignRole($userRole));

        // Permanent admin account
        $admin = User::factory()->create([
            'name'  => 'Admin',
            'email' => 'admin@larablog.test',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');
    }
}