<?php

namespace Modules\Articles\Services;

use Modules\Articles\Models\Post;
use Modules\Articles\Actions\MapPostRelationsAction;
use Modules\Articles\Http\Resources\PostPublicResource;
use Modules\Articles\Services\Contracts\PostPublicServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class PostPublicService implements PostPublicServiceInterface
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

    public function getBySlug(string $slug): ?object
    {
        $post = Post::with(['category', 'tags'])
            ->where('slug', $slug)
            ->published()
            ->first();

        if (!$post) return null;

        $post->increment('views');
        $this->mapPostRelations->execute([$post]);
        return $post;
    }

    public function getRelatedPosts(string $slug, int $limit = 3): array
    {
        $post = Post::where('slug', $slug)->published()->first();
        if (!$post) return [];

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

    public function getPostsByCategory(string $categoryId, ?string $sort = 'newest', int $perPage = 10): LengthAwarePaginator
    {
        $query = Post::with(['category', 'tags'])
            ->published()
            ->byCategory($categoryId);

        $this->applyPublicSort($query, $sort);

        $posts = $query->paginate($perPage);
        $this->mapPostRelations->execute($posts);
        return $posts;
    }

    public function getPostsByTag(string $tagId, ?string $sort = 'newest', int $perPage = 10): LengthAwarePaginator
    {
        $query = Post::with(['category', 'tags'])
            ->published()
            ->whereHas('tags', fn($q) => $q->whereKey($tagId));

        $this->applyPublicSort($query, $sort);

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

        $this->applyPublicSort($query, $sort);

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

    public function getFeaturedPosts(int $limit = 4): Collection
    {
        $posts = Post::with(['category', 'tags'])
            ->published()
            ->where('is_editors_pick', true)
            ->latest('published_at')
            ->take($limit)
            ->get();

        $this->mapPostRelations->execute($posts);
        return $posts;
    }

    public function getRecentPosts(int $limit = 6, array $excludeIds = []): Collection
    {
        $query = Post::with(['category', 'tags'])
            ->published()
            ->when(!empty($excludeIds), fn($q) => $q->whereNotIn('id', $excludeIds))
            ->latest('published_at')
            ->take($limit);

        $posts = $query->get();
        $this->mapPostRelations->execute($posts);
        return $posts;
    }

    public function getTotalPostsCount(): int
    {
        return Post::count();
    }

    public function getPublishedPostsCount(): int
    {
        return Post::published()->count();
    }

    public function getTotalViews(): int
    {
        return (int) Post::sum('views');
    }

    public function getAuthorStats(): array
    {
        return Post::selectRaw('user_id, COUNT(*) as posts_count, SUM(views) as total_views')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id')
            ->toArray();
    }

    public function getPublishedPostsByCategoryForPublic(string $categorySlug, ?string $sort = 'newest', int $perPage = 10): array
    {
        $query = Post::with(['category', 'tags'])
            ->published()
            ->whereHas('category', fn($q) => $q->where('slug', $categorySlug));

        $this->applyPublicSort($query, $sort);

        $posts = $query->paginate($perPage);
        $this->mapPostRelations->execute($posts);
        
        return $this->formatPostsArray($posts);
    }

    public function getPublishedPostsByTagForPublic(string $tagSlug, ?string $sort = 'newest', int $perPage = 10): array
    {
        $query = Post::with(['category', 'tags'])
            ->published()
            ->whereHas('tags', fn($q) => $q->where('slug', $tagSlug));

        $this->applyPublicSort($query, $sort);

        $posts = $query->paginate($perPage);
        $this->mapPostRelations->execute($posts);
        
        return $this->formatPostsArray($posts);
    }

    public function getPublishedPostCountsByCategories(array $categoryIds): array
    {
        if (empty($categoryIds)) return [];
        
        return Post::select('category_id')
            ->selectRaw('count(*) as count')
            ->whereIn('category_id', $categoryIds)
            ->published()
            ->groupBy('category_id')
            ->pluck('count', 'category_id')
            ->toArray();
    }

    public function getDistinctAuthorCountsByCategories(array $categoryIds): array
    {
        if (empty($categoryIds)) return [];
        
        return Post::select('category_id')
            ->selectRaw('count(distinct user_id) as count')
            ->whereIn('category_id', $categoryIds)
            ->published()
            ->groupBy('category_id')
            ->pluck('count', 'category_id')
            ->toArray();
    }

    public function getPopularCategoryStats(int $limit): array
    {
        return Post::select('category_id')
            ->selectRaw('count(*) as posts_count')
            ->published()
            ->whereNotNull('category_id')
            ->groupBy('category_id')
            ->orderByDesc('posts_count')
            ->limit($limit)
            ->get()
            ->map(fn($row) => [
                'category_id' => $row->category_id,
                'posts_count' => (int) $row->posts_count,
            ])
            ->toArray();
    }

    public function getPublishedPostCountsByTags(array $tagIds): array
    {
        if (empty($tagIds)) return [];
        
        return \Illuminate\Support\Facades\DB::table('content_post_tag')
            ->select('tag_id')
            ->selectRaw('count(*) as count')
            ->whereIn('tag_id', $tagIds)
            ->whereExists(function ($query) {
                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('content_posts')
                    ->whereColumn('content_posts.id', 'content_post_tag.post_id')
                    ->where('content_posts.is_published', true);
            })
            ->groupBy('tag_id')
            ->pluck('count', 'tag_id')
            ->toArray();
    }

    public function getPublishedPostViewsSumByTags(array $tagIds): array
    {
        if (empty($tagIds)) return [];
        
        return \Illuminate\Support\Facades\DB::table('content_post_tag')
            ->join('content_posts', 'content_posts.id', '=', 'content_post_tag.post_id')
            ->select('content_post_tag.tag_id')
            ->selectRaw('COALESCE(sum(content_posts.views), 0) as total_views')
            ->whereIn('content_post_tag.tag_id', $tagIds)
            ->where('content_posts.is_published', true)
            ->groupBy('content_post_tag.tag_id')
            ->pluck('total_views', 'tag_id')
            ->toArray();
    }

    public function getPopularTagStats(int $limit): array
    {
        return \Illuminate\Support\Facades\DB::table('content_post_tag')
            ->join('content_posts', 'content_posts.id', '=', 'content_post_tag.post_id')
            ->select('content_post_tag.tag_id')
            ->selectRaw('count(*) as posts_count')
            ->selectRaw('COALESCE(sum(content_posts.views), 0) as total_views')
            ->where('content_posts.is_published', true)
            ->groupBy('content_post_tag.tag_id')
            ->orderByDesc('total_views')
            ->limit($limit)
            ->get()
            ->map(fn($row) => [
                'tag_id'      => $row->tag_id,
                'posts_count' => (int) $row->posts_count,
                'total_views' => (int) $row->total_views,
            ])
            ->toArray();
    }

    /**
     * Apply common public sorting logic.
     */
    private function applyPublicSort(Builder $query, ?string $sort): void
    {
        match ($sort) {
            'oldest'       => $query->oldest('updated_at'),
            'most_popular' => $query->popular(),
            default        => $query->latest('updated_at'),
        };
    }

    private function formatPostsArray(LengthAwarePaginator $paginator): array
    {
        return PostPublicResource::collection($paginator)
            ->response()
            ->getData(true);
    }
}