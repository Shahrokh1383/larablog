<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

pest()->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature')
    ->beforeEach(function () {
        // Seed roles needed by the application
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'editor', 'guard_name' => 'web']);
        Role::create(['name' => 'author', 'guard_name' => 'web']);
        Role::create(['name' => 'user', 'guard_name' => 'web']);
    });

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});