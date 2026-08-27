<?php

namespace Modules\Articles\Services;

use Modules\Articles\Models\Post;
use Modules\Articles\Actions\MapPostRelationsAction;
use Modules\Articles\Services\Contracts\PostPublicServiceInterface;
use Modules\Taxonomy\Services\Contracts\CategoryPublicServiceInterface;
use Modules\Taxonomy\Services\Contracts\TagPublicServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class PostPublicService implements PostPublicServiceInterface
{
    public function __construct(
        private MapPostRelationsAction $mapPostRelations,
        private TagPublicServiceInterface $tagService,
        private CategoryPublicServiceInterface $categoryService,
    ) {}

    public function getPaginatedPosts(int $perPage = 10): LengthAwarePaginator
    {
        $posts = Post::published()
            ->latest('published_at')
            ->paginate($perPage);

        $this->mapPostRelations->execute($posts);

        return $posts;
    }

    public function getBySlug(string $slug): ?object
    {
        $post = Post::where('slug', $slug)
            ->published()
            ->first();

        if (!$post) {
            return null;
        }

        $post->increment('views');
        $this->mapPostRelations->execute([$post]);

        return $post;
    }

    public function getRelatedPosts(string $slug, int $limit = 3): array
    {
        $post = Post::where('slug', $slug)->published()->first();

        if (!$post) {
            return [];
        }

        $related = Post::published()
            ->where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->latest('published_at')
            ->take($limit)
            ->get();

        $this->mapPostRelations->execute($related);

        return $related->all();
    }

    public function getPostsByAuthor(string $username, ?string $sort = 'newest', int $perPage = 6): LengthAwarePaginator
    {
        $user = \Shared\Models\User::where('username', $username)->firstOrFail();

        $query = Post::published()->where('user_id', $user->id);
        $this->applyPublicSort($query, $sort);

        $posts = $query->paginate($perPage);
        $this->mapPostRelations->execute($posts);

        return $posts;
    }

    public function searchPosts(?string $term, int $perpage = 10): LengthAwarePaginator
    {
        $posts = Post::published()
            ->search($term)
            ->latest('published_at')
            ->paginate($perpage);

        $this->mapPostRelations->execute($posts);

        return $posts;
    }

    public function getFeaturedPosts(int $limit = 4): Collection
    {
        $posts = Post::published()
            ->where('is_editors_pick', true)
            ->latest('published_at')
            ->take($limit)
            ->get();

        $this->mapPostRelations->execute($posts);

        return $posts;
    }

    public function getRecentPosts(int $limit = 6, array $excludeIds = []): Collection
    {
        $query = Post::published()
            ->when(!empty($excludeIds), fn($q) => $q->whereNotIn('id', $excludeIds))
            ->latest('published_at')
            ->take($limit);

        $posts = $query->get();
        $this->mapPostRelations->execute($posts);

        return $posts;
    }

    public function getPublishedPostsByCategoryForPublic(string $categorySlug, ?string $sort = 'newest', int $perPage = 10): array
    {
        $categoryMeta = $this->categoryService->getCategoryMetaBySlug($categorySlug);
        $categoryId = $categoryMeta['id'];

        $query = Post::published()->byCategory($categoryId);
        $this->applyPublicSort($query, $sort);

        $posts = $query->paginate($perPage);
        $this->mapPostRelations->execute($posts);

        return [
            'category' => $categoryMeta,
            'posts' => $posts,
        ];
    }

    public function getPublishedPostsByTagForPublic(string $tagSlug, ?string $sort = 'newest', int $perPage = 10): array
    {
        $tagMeta = $this->tagService->getTagMetaBySlug($tagSlug);
        $tagId = $tagMeta['id'];

        $query = Post::published();
        $query = $this->tagService->applyTagPostFilter($query, $tagId);
        $this->applyPublicSort($query, $sort);

        $posts = $query->paginate($perPage);
        $this->mapPostRelations->execute($posts);

        return [
            'tag' => $tagMeta,
            'posts' => $posts,
        ];
    }

    private function applyPublicSort(Builder $query, ?string $sort): void
    {
        match ($sort) {
            'oldest'       => $query->oldest('published_at'),
            'most_popular' => $query->popular(),
            default        => $query->latest('published_at'),
        };
    }
}