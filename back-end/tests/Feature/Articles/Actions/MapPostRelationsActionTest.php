<?php

use Modules\Identity\Models\User;
use Modules\Articles\Models\Post;
use Modules\Taxonomy\Models\Category;
use Modules\Articles\Actions\MapPostRelationsAction;
use Modules\Profile\Services\Contracts\FetchesPublicProfiles;
use Modules\Engagement\Services\Contracts\CommentServiceInterface;
use Modules\ReaderExperience\Services\Contracts\SavedPostInteractionContract;
use Modules\Taxonomy\Services\Contracts\CategoryPublicServiceInterface;
use Modules\Taxonomy\Services\Contracts\TagPublicServiceInterface;
use Mockery\MockInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

it('maps relations onto posts', function () {
    $author = User::factory()->create();
    $category = Category::factory()->create();
    $post = Post::factory()->create([
        'user_id'     => $author->id,
        'category_id' => $category->id,
    ]);

    $this->mock(FetchesPublicProfiles::class, function (MockInterface $mock) use ($author) {
        $mock->shouldReceive('getPublicProfilesMap')
            ->once()
            ->with([$author->id])
            ->andReturn([$author->id => ['id' => $author->id, 'name' => $author->name]]);
    });

    $this->mock(CommentServiceInterface::class, function (MockInterface $mock) use ($post) {
        $mock->shouldReceive('getCommentCountsForPosts')
            ->once()
            ->with([$post->id])
            ->andReturn([$post->id => 3]);
    });

    $this->mock(SavedPostInteractionContract::class, function (MockInterface $mock) use ($author, $post) {
        $mock->shouldReceive('getSavedPostIdsForUser')
            ->once()
            ->with($author->id, [$post->id])
            ->andReturn([$post->id]);
    });

    $this->mock(CategoryPublicServiceInterface::class, function (MockInterface $mock) use ($post, $category) {
        $mock->shouldReceive('getCategoriesByIds')
            ->once()
            ->with([$category->id])
            ->andReturn([
                $category->id => [
                    'id'   => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ],
            ]);
    });

    $this->mock(TagPublicServiceInterface::class, function (MockInterface $mock) use ($post) {
        $mock->shouldReceive('getTagsByPostIds')
            ->once()
            ->with([$post->id])
            ->andReturn([$post->id => []]);
    });

    Auth::shouldReceive('user')->andReturn($author);

    app(MapPostRelationsAction::class)->execute(new Collection([$post]));

    expect($post->author)->toMatchArray(['id' => $author->id])
        ->and($post->comments_count)->toBe(3)
        ->and($post->is_saved)->toBeTrue()
        ->and($post->category_detail)->toMatchArray([
            'id'   => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
        ])
        ->and($post->tags_detail)->toBe([]);
});

it('does nothing for empty posts', function () {
    $this->mock(FetchesPublicProfiles::class);
    $this->mock(CommentServiceInterface::class);
    $this->mock(SavedPostInteractionContract::class);
    $this->mock(CategoryPublicServiceInterface::class);
    $this->mock(TagPublicServiceInterface::class);

    Auth::shouldReceive('user')->andReturnNull();

    app(MapPostRelationsAction::class)->execute(new Collection());

    // If no exception thrown, test passes.
    expect(true)->toBeTrue();
});