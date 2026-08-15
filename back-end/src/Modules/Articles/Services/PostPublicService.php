<?php

namespace Modules\Articles\Services;

use Modules\Articles\Models\Post;
use Modules\Content\Models\Category;
use Modules\Content\Models\Tag;
use Modules\Articles\Actions\MapPostRelationsAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PostPublicService
{
    public function __construct(
        private MapPostRelationsAction $mapPostRelations,
    ) {}

    public function getPaginatedPosts(int $perPage = 10): LengthAwarePaginator
    {
        $posts = Post::with(['category', 'tags'])
            ->published()
            ->latest('published_at')
            ->paginate($perPage);

        $this->mapPostRelations->execute($posts);

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

        $related = Post::with(['category', 'tags'])
            ->published()
            ->where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->latest('published_at')
            ->take($limit)
            ->get();

        $this->mapPostRelations->execute($related);

        return $related->all();
    }

    public function getPostsByCategory(string $categorySlug, ?string $sort = 'newest', int $perPage = 10): LengthAwarePaginator
    {
        $category = Category::where('slug', $categorySlug)->firstOrFail();

        $query = Post::with(['category', 'tags'])
            ->published()
            ->byCategory($category->id);

        match ($sort) {
            'oldest'       => $query->oldest('updated_at'),
            'most_popular' => $query->popular(),
            default        => $query->latest('updated_at'),
        };

        $posts = $query->paginate($perPage);
        $this->mapPostRelations->execute($posts);

        return $posts;
    }

    public function getPostsByTag(string $tagSlug, ?string $sort = 'newest', int $perPage = 10): LengthAwarePaginator
    {
        $tag = Tag::where('slug', $tagSlug)->firstOrFail();

        $query = Post::with(['category', 'tags'])
            ->published()
            ->whereHas('tags', fn($q) => $q->whereKey($tag->id));

        match ($sort) {
            'oldest'       => $query->oldest('updated_at'),
            'most_popular' => $query->popular(),
            default        => $query->latest('updated_at'),
        };

        $posts = $query->paginate($perPage);
        $this->mapPostRelations->execute($posts);

        return $posts;
    }

    public function getPostsByAuthor(string $username, ?string $sort = 'newest', int $perPage = 6): LengthAwarePaginator
    {
        $user = \Shared\Models\User::where('username', $username)->firstOrFail();

        $query = Post::with(['category', 'tags'])
            ->published()
            ->where('user_id', $user->id);

        match ($sort) {
            'oldest'       => $query->oldest('updated_at'),
            'most_popular' => $query->popular(),
            default        => $query->latest('updated_at'),
        };

        $posts = $query->paginate($perPage);
        $this->mapPostRelations->execute($posts);

        return $posts;
    }

    public function searchPosts(?string $term, int $perpage = 10): LengthAwarePaginator
    {
        $posts = Post::with(['category', 'tags'])
            ->published()
            ->search($term)
            ->latest('published_at')
            ->paginate($perpage);
            
        $this->mapPostRelations->execute($posts);
        
        return $posts;
    }
}