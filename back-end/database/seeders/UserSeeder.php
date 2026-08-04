<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Identity\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    private const ADMIN_COUNT = 5;
    private const EDITOR_COUNT = 30;
    private const AUTHOR_COUNT = 100;
    private const USER_COUNT = 1000;

    public function run(): void
    {
        // Ensure roles exist (RoleSeeder must run first)
        $adminRole = Role::findByName('admin', 'web');
        $editorRole = Role::findByName('editor', 'web');
        $authorRole = Role::findByName('author', 'web');
        $userRole = Role::findByName('user', 'web');

        // Create admin users
        User::factory()->count(self::ADMIN_COUNT)->create()->each(
            fn (User $u) => $u->assignRole($adminRole)
        );

        // Create editor users
        User::factory()->count(self::EDITOR_COUNT)->create()->each(
            fn (User $u) => $u->assignRole($editorRole)
        );

        // Create author users
        User::factory()->count(self::AUTHOR_COUNT)->create()->each(
            fn (User $u) => $u->assignRole($authorRole)
        );

        // Create regular users
        User::factory()->count(self::USER_COUNT)->create()->each(
            fn (User $u) => $u->assignRole($userRole)
        );

        // Permanent admin account for convenience
        $admin = User::factory()->create([
            'name'     => 'Admin',
            'email'    => 'admin@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole($adminRole);
    }
}