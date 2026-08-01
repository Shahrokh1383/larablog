<?php

use Modules\Identity\Models\User;
use Modules\Content\Models\Tag;
use Spatie\Permission\Models\Role;
use function Pest\Laravel\{actingAs, getJson, postJson, putJson, deleteJson};

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);
    Role::create(['name' => 'editor', 'guard_name' => 'web']);
    Role::create(['name' => 'author', 'guard_name' => 'web']);
});

it('editor can create a tag', function () {
    $editor = User::factory()->create()->assignRole('editor');
    actingAs($editor)
        ->postJson('/admin/tags', ['name' => 'php'])
        ->assertCreated();
});

it('author cannot create a tag', function () {
    $author = User::factory()->create()->assignRole('author');
    actingAs($author)
        ->postJson('/admin/tags', ['name' => 'php'])
        ->assertForbidden();
});

it('admin can delete a tag', function () {
    $admin = User::factory()->create()->assignRole('admin');
    $tag = Tag::factory()->create();
    actingAs($admin)
        ->deleteJson("/admin/tags/{$tag->id}")
        ->assertNoContent();
});