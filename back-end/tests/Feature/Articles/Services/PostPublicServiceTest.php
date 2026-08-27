<?php

use Modules\Identity\Models\User;
use Modules\Articles\Models\Post;
use Modules\Articles\Services\PostPublicService;
use Modules\Articles\Actions\MapPostRelationsAction;
use Modules\Taxonomy\Models\Category;
use Modules\Taxonomy\Models\Tag;
use Modules\Taxonomy\Services\Contracts\CategoryPublicServiceInterface;
use Modules\Taxonomy\Services\Contracts\TagPublicServiceInterface;
use Mockery\MockInterface;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->mock(MapPostRelationsAction::class, function (MockInterface $mock) {
        $mock->shouldReceive('execute')->andReturnNull();
    });

    $this->mock(CategoryPublicServiceInterface::class);
    $this->mock(TagPublicServiceInterface::class);
});

it('paginates published posts', function () {
    Post::factory()->count(2)->create(['is_published' => true]);
    Post::factory()->create(['is_published' => false]);

    $service = app(PostPublicService::class);
    $paginator = $service->getPaginatedPosts(10);

    expect($paginator->total())->toBe(2);
});

it('returns post by slug and increments views', function () {
    $post = Post::factory()->create(['is_published' => true, 'views' => 5]);

    $service = app(PostPublicService::class);
    $result = $service->getBySlug($post->slug);

    expect($result)->toBeObject()
        ->and($result->id)->toBe($post->id)
        ->and($post->fresh()->views)->toBe(6);
});

it('returns null for missing slug', function () {
    $service = app(PostPublicService::class);

    expect($service->getBySlug('missing-slug'))->toBeNull();
});

it('returns related posts by same category', function () {
    $cat = Category::factory()->create();
    $post = Post::factory()->create(['category_id' => $cat->id, 'is_published' => true]);
    $related = Post::factory()->create(['category_id' => $cat->id, 'is_published' => true]);
    Post::factory()->create(['category_id' => $cat->id, 'is_published' => false]);

    $service = app(PostPublicService::class);
    $result = $service->getRelatedPosts($post->slug);

    expect($result)->toHaveCount(1)
        ->and($result[0]->id)->toBe($related->id);
});

it('returns posts by author username', function () {
    $user = User::factory()->create(['username' => 'jane']);
    Post::factory()->count(2)->create(['user_id' => $user->id, 'is_published' => true]);

    $service = app(PostPublicService::class);
    $paginator = $service->getPostsByAuthor('jane');

    expect($paginator->total())->toBe(2);
});

it('searches posts by term', function () {
    Post::factory()->create(['title' => 'Laravel article', 'is_published' => true]);
    Post::factory()->create(['title' => 'PHP tips', 'is_published' => true]);

    $service = app(PostPublicService::class);
    $paginator = $service->searchPosts('Laravel');

    expect($paginator->total())->toBe(1);
});

it('returns featured posts', function () {
    Post::factory()->create(['is_published' => true, 'is_editors_pick' => true]);
    Post::factory()->create(['is_published' => true, 'is_editors_pick' => false]);

    $service = app(PostPublicService::class);
    $result = $service->getFeaturedPosts(10);

    expect($result)->toHaveCount(1);
});

it('returns recent posts excluding ids', function () {
    $post1 = Post::factory()->create(['is_published' => true]);
    $post2 = Post::factory()->create(['is_published' => true]);

    $service = app(PostPublicService::class);
    $result = $service->getRecentPosts(10, [$post1->id]);

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($post2->id);
});

it('returns published posts by category slug', function () {
    $cat = Category::factory()->create(['slug' => 'my-category']);
    Post::factory()->create(['category_id' => $cat->id, 'is_published' => true]);
    Post::factory()->create(['category_id' => $cat->id, 'is_published' => false]);

    $this->mock(CategoryPublicServiceInterface::class, function (MockInterface $mock) use ($cat) {
        $mock->shouldReceive('getCategoryMetaBySlug')
            ->once()
            ->with('my-category')
            ->andReturn([
                'id' => (string) $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'posts_count' => 1,
                'authors_count' => 1,
            ]);
    });

    $service = app(PostPublicService::class);
    $result = $service->getPublishedPostsByCategoryForPublic('my-category');

    expect($result['category']['slug'])->toBe('my-category')
        ->and($result['posts']->total())->toBe(1);
});

it('returns published posts by tag slug', function () {
    $tag = Tag::factory()->create(['slug' => 'my-tag']);
    $post = Post::factory()->create(['is_published' => true]);
    Post::factory()->create(['is_published' => false]);
    DB::table('content_post_tag')->insert([
        ['post_id' => $post->id, 'tag_id' => $tag->id],
    ]);

    $this->mock(TagPublicServiceInterface::class, function (MockInterface $mock) use ($tag) {
        $mock->shouldReceive('getTagMetaBySlug')
            ->once()
            ->with('my-tag')
            ->andReturn([
                'id' => (string) $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'posts_count' => 1,
            ]);

        $mock->shouldReceive('applyTagPostFilter')
            ->once()
            ->andReturnUsing(fn ($query) => $query);
    });

    $service = app(PostPublicService::class);
    $result = $service->getPublishedPostsByTagForPublic('my-tag');

    expect($result['tag']['slug'])->toBe('my-tag')
        ->and($result['posts']->total())->toBe(1);
});