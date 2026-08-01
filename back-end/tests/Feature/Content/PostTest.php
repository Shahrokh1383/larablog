<?php

use Modules\Identity\Models\User;
use Modules\Content\Models\Post;
use Modules\Content\Models\Category;
use Modules\Content\Models\Tag;
use Spatie\Permission\Models\Role;
use function Pest\Laravel\{actingAs, getJson, postJson, putJson, deleteJson};

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'web']);
    Role::create(['name' => 'editor', 'guard_name' => 'web']);
    Role::create(['name' => 'author', 'guard_name' => 'web']);
});

it('author can create a post', function () {
    $author = User::factory()->create()->assignRole('author');
    actingAs($author)
        ->postJson('/admin/posts', [
            'title' => 'My Post',
            'body'  => 'Lorem ipsum dolor sit amet.',
        ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'My Post')
        ->assertJsonPath('data.reading_time', 1);
});

it('author can update own post', function () {
    $author = User::factory()->create()->assignRole('author');
    $post = Post::factory()->create(['user_id' => $author->id]);
    actingAs($author)
        ->putJson("/admin/posts/{$post->id}", ['title' => 'Updated Title'])
        ->assertOk()
        ->assertJsonPath('data.title', 'Updated Title');
});

it('author cannot update another author post', function () {
    $author1 = User::factory()->create()->assignRole('author');
    $author2 = User::factory()->create()->assignRole('author');
    $post = Post::factory()->create(['user_id' => $author1->id]);
    actingAs($author2)
        ->putJson("/admin/posts/{$post->id}", ['title' => 'Hacked'])
        ->assertForbidden();
});

it('editor can update any post', function () {
    $editor = User::factory()->create()->assignRole('editor');
    $author = User::factory()->create()->assignRole('author');
    $post = Post::factory()->create(['user_id' => $author->id]);
    actingAs($editor)
        ->putJson("/admin/posts/{$post->id}", ['title' => 'Editor Change'])
        ->assertOk();
});

it('admin can delete any post', function () {
    $admin = User::factory()->create()->assignRole('admin');
    $author = User::factory()->create()->assignRole('author');
    $post = Post::factory()->create(['user_id' => $author->id]);
    actingAs($admin)
        ->deleteJson("/admin/posts/{$post->id}")
        ->assertNoContent();
});

it('author can delete own post', function () {
    $author = User::factory()->create()->assignRole('author');
    $post = Post::factory()->create(['user_id' => $author->id]);
    actingAs($author)
        ->deleteJson("/admin/posts/{$post->id}")
        ->assertNoContent();
});