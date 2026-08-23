<?php

use Modules\Identity\Models\User;
use Modules\Articles\Models\Post;
use Modules\Articles\Services\PostService;
use Modules\Articles\Actions\AssignTagsToPostAction;
use Modules\Articles\Actions\UploadImageAction;
use Modules\Articles\Actions\DeleteImageAction;
use Modules\Articles\Actions\MapPostRelationsAction;
use Modules\Articles\DTOs\PostCreateDTO;
use Modules\Articles\DTOs\PostUpdateDTO;
use Modules\Taxonomy\Models\Tag;
use Mockery\MockInterface;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->mock(AssignTagsToPostAction::class, function (MockInterface $mock) {
        $mock->shouldReceive('execute')->andReturnNull();
    });
    $this->mock(UploadImageAction::class);
    $this->mock(DeleteImageAction::class);
    $this->mock(MapPostRelationsAction::class, function (MockInterface $mock) {
        $mock->shouldReceive('execute')->andReturnNull();
    });
});

it('lists all posts for admin and editor', function () {
    $author = User::factory()->create();
    $other = User::factory()->create();
    Post::factory()->count(2)->create(['user_id' => $author->id]);
    Post::factory()->create(['user_id' => $other->id]);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $service = app(PostService::class);
    $result = $service->getAll(null, $admin, 15, 1, null);

    expect($result->total())->toBe(3);
});

it('filters list by author role', function () {
    $author = User::factory()->create();
    $other = User::factory()->create();
    Post::factory()->count(2)->create(['user_id' => $author->id]);
    Post::factory()->create(['user_id' => $other->id]);

    $authorUser = $author;
    $authorUser->assignRole('author');

    $service = app(PostService::class);
    $result = $service->getAll(null, $authorUser, 15, 1, null);

    expect($result->total())->toBe(2)
        ->and($result->items()[0]->user_id)->toBe($author->id);
});

it('creates a post with generated slug and reading time', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->create();
    $dto = new PostCreateDTO(
        title: 'Hello World Post',
        body: 'word '.str_repeat('word ', 399),
        userId: $user->id,
        isPublished: true,
        tagIds: [$tag->id],
    );

    $service = app(PostService::class);
    $post = $service->create($dto);

    expect($post)->toBeInstanceOf(Post::class)
        ->and($post->title)->toBe('Hello World Post')
        ->and($post->slug)->toBe('hello-world-post')
        ->and($post->reading_time)->toBe(2)
        ->and($post->is_published)->toBeTrue()
        ->and($post->published_at)->not->toBeNull();
});

it('updates a post and regenerates slug when title changes', function () {
    $post = Post::factory()->create(['title' => 'Old Title', 'slug' => 'old-title', 'body' => 'old body']);
    $dto = new PostUpdateDTO(title: 'New Title', body: 'new body '.str_repeat('word ', 198));

    $service = app(PostService::class);
    $updated = $service->update($post, $dto);

    expect($updated->title)->toBe('New Title')
        ->and($updated->slug)->toBe('new-title')
        ->and($updated->reading_time)->toBe(1);
});

it('keeps slug when title unchanged', function () {
    $post = Post::factory()->create(['title' => 'Same Title', 'slug' => 'same-title']);
    $dto = new PostUpdateDTO(title: 'Same Title');

    $service = app(PostService::class);
    $updated = $service->update($post, $dto);

    expect($updated->slug)->toBe('same-title');
});

it('deletes a post', function () {
    $post = Post::factory()->create();

    $service = app(PostService::class);
    $service->delete($post);

    expect(Post::find($post->id))->toBeNull();
});

it('finds a post by id', function () {
    $post = Post::factory()->create();

    $service = app(PostService::class);
    $result = $service->find($post->id);

    expect($result)->toBeInstanceOf(Post::class)
        ->and($result->id)->toBe($post->id);
});

it('returns null for missing find id', function () {
    $service = app(PostService::class);

    expect($service->find('missing-id'))->toBeNull();
});

it('enriches post with user and relations', function () {
    $post = Post::factory()->create();

    $service = app(PostService::class);
    $result = $service->enrich($post);

    expect($result->relationLoaded('user'))->toBeTrue();
});

it('uploads an image via action', function () {
    $file = UploadedFile::fake()->image('post.jpg');
    $this->mock(UploadImageAction::class, function (MockInterface $mock) use ($file) {
        $mock->shouldReceive('execute')
            ->once()
            ->with($file)
            ->andReturn('http://localhost/storage/posts/images/post.jpg');
    });

    $service = app(PostService::class);
    $result = $service->uploadImage($file);

    expect($result)->toBe('http://localhost/storage/posts/images/post.jpg');
});

it('deletes image via action', function () {
    $this->mock(DeleteImageAction::class, function (MockInterface $mock) {
        $mock->shouldReceive('execute')
            ->once()
            ->with('http://localhost/storage/posts/images/post.jpg')
            ->andReturnTrue();
    });

    $service = app(PostService::class);
    $result = $service->deleteImage('http://localhost/storage/posts/images/post.jpg');

    expect($result)->toBeTrue();
});