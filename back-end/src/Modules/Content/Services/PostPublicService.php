<?php

namespace Modules\Content\Services;

use Modules\Content\Models\Post;
use Modules\Content\Models\Category;
use Modules\Content\Models\Tag;
use Modules\Profile\Services\Contracts\FetchesPublicProfiles;
use Modules\Engagement\Services\Contracts\CommentServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PostPublicService
{
    public function __construct(
        private FetchesPublicProfiles $profileService,
        private CommentServiceInterface $commentService,
    ) {}

    public function getHomeData(int $perPage = 10): LengthAwarePaginator
    {
        $posts = Post::with(['category', 'tags'])
            ->published()
            ->latest('published_at')
            ->paginate($perPage);

        $this->mapAuthorsToPosts($posts);
        $this->mapCommentsToPosts($posts);

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

        $this->mapAuthorsToPosts([$post]);
        $this->mapCommentsToPosts([$post]);

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
        $this->mapCommentsToPosts($related);

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
        $this->mapAuthorsToPosts($posts);
        $this->mapCommentsToPosts($posts);

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
        $this->mapAuthorsToPosts($posts);
        $this->mapCommentsToPosts($posts);

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
        $this->mapAuthorsToPosts($posts);
        $this->mapCommentsToPosts($posts);

        return $posts;
    }

    private function mapAuthorsToPosts(LengthAwarePaginator|Collection|array $posts): void
    {
        $postsCollection = $posts instanceof LengthAwarePaginator 
            ? collect($posts->items()) 
            : collect($posts);

        $authorIds = $postsCollection->pluck('user_id')->unique()->filter()->values()->toArray();
        if (empty($authorIds)) return;

        $profilesMap = $this->profileService->getPublicProfilesMap($authorIds);
        $postsCollection->each(function (Post $post) use ($profilesMap) {
            $post->author = $profilesMap[$post->user_id] ?? null;
        });
    }

    private function mapCommentsToPosts(LengthAwarePaginator|Collection|array $posts): void
    {
        $postsCollection = $posts instanceof LengthAwarePaginator 
            ? collect($posts->items()) 
            : collect($posts);

        $postIds = $postsCollection->pluck('id')->toArray();
        if (empty($postIds)) return;

        $counts = $this->commentService->getCommentCountsForPosts($postIds);
    
        $postsCollection->each(function (Post $post) use ($counts) {
            $post->comments_count = $counts[$post->id] ?? 0;
        });
    }
}