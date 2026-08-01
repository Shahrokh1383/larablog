<?php

use Modules\Identity\Models\User;
use Modules\Content\Models\Category;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\{actingAs, getJson, postJson, putJson, deleteJson};

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);
    Role::create(['name' => 'editor', 'guard_name' => 'web']);
    Role::create(['name' => 'author', 'guard_name' => 'web']);
});

it('admin can list categories', function () {
    $admin = User::factory()->create()->assignRole('admin');
    Category::factory()->count(3)->create();
    actingAs($admin)
        ->getJson('/api/admin/categories')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('editor can create a category', function () {
    $editor = User::factory()->create()->assignRole('editor');
    actingAs($editor)
        ->postJson('/api/admin/categories', ['name' => 'Laravel'])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Laravel');
});

it('author cannot create a category', function () {
    $author = User::factory()->create()->assignRole('author');
    actingAs($author)
        ->postJson('/api/admin/categories', ['name' => 'Laravel'])
        ->assertForbidden();
});

it('admin can update a category', function () {
    $admin = User::factory()->create()->assignRole('admin');
    $category = Category::factory()->create();
    actingAs($admin)
        ->putJson("/api/admin/categories/{$category->id}", ['name' => 'PHP'])
        ->assertOk()
        ->assertJsonPath('data.name', 'PHP');
});

it('admin can delete a category', function () {
    $admin = User::factory()->create()->assignRole('admin');
    $category = Category::factory()->create();
    actingAs($admin)
        ->deleteJson("/api/admin/categories/{$category->id}")
        ->assertNoContent();
    expect(Category::count())->toBe(0);
});