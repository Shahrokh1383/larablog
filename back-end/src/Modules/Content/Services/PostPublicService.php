<?php

namespace Modules\Content\Services;

use Modules\Content\Models\Post;
use Modules\Content\Models\Category;
use Modules\Identity\Services\Contracts\AuthorServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PostPublicService
{
    public function __construct(
        private AuthorServiceInterface $authorService,
    ) {}

    public function getHomeData(int $perPage = 10): LengthAwarePaginator
    {
        $posts = Post::with(['category', 'tags'])
            ->published()
            ->latest('published_at')
            ->paginate($perPage);

        $this->mapAuthorsToPosts($posts);

        return $posts;
    }

    public function getBySlug(string $slug): ?Post
    {
        $post = Post::with(['category', 'tags'])
            ->where('slug', $slug)
            ->published()
            ->first();

        if (!$post) {
            return null;
        }

        $this->mapAuthorsToPosts([$post]);

        return $post;
    }

    public function getRelatedPosts(string $slug, int $limit = 3): array
    {
        $post = Post::where('slug', $slug)->published()->first();
        if (!$post) {
            return [];
        }

        $related = Post::with(['category', 'tags'])
            ->published()
            ->where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->latest('published_at')
            ->take($limit)
            ->get();

        $this->mapAuthorsToPosts($related);

        return $related->all();
    }

    public function getPostsByCategory(string $categorySlug, ?string $sort = 'newest', int $perPage = 10): LengthAwarePaginator
    {
        $category = Category::where('slug', $categorySlug)->firstOrFail();

        $query = Post::with(['category', 'tags'])
            ->published()
            ->byCategory($category->id);

        match ($sort) {
            'oldest'       => $query->oldest('published_at'),
            'most_popular' => $query->popular(),
            default        => $query->latest('published_at'),
        };

        $posts = $query->paginate($perPage);
        $this->mapAuthorsToPosts($posts);

        return $posts;
    }

    /**
     * Map author data to posts.
     *
     * @param LengthAwarePaginator|Collection|array $posts
     * @return void
     */
    private function mapAuthorsToPosts(LengthAwarePaginator|Collection|array $posts): void
    {
        // Use items() for Paginator (interface compatible), wrap the rest in a collection
        $postsCollection = $posts instanceof LengthAwarePaginator 
            ? collect($posts->items()) 
            : collect($posts);

        $authorIds = $postsCollection->pluck('user_id')->unique()->toArray();
        if (empty($authorIds)) return;

        $authors = $this->authorService->getByUserIds($authorIds);

        $postsCollection->each(function (Post $post) use ($authors) {
            $post->author = isset($authors[$post->user_id])
                ? $authors[$post->user_id]->toArray()
                : null;
        });
    }
}