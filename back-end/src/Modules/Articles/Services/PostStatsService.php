<?php

namespace Modules\Articles\Services;

use Modules\Articles\Models\Post;
use Modules\Articles\Services\Contracts\PostStatsServiceInterface;
use Illuminate\Support\Facades\DB;

class PostStatsService implements PostStatsServiceInterface
{
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

    public function getPublishedPostCountsByCategories(array $categoryIds): array
    {
        if (empty($categoryIds)) {
            return [];
        }
        
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
        if (empty($categoryIds)) {
            return [];
        }
        
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
        if (empty($tagIds)) {
            return [];
        }
        
        return DB::table('content_post_tag')
            ->select('tag_id')
            ->selectRaw('count(*) as count')
            ->whereIn('tag_id', $tagIds)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
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
        if (empty($tagIds)) {
            return [];
        }
        
        return DB::table('content_post_tag')
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
        return DB::table('content_post_tag')
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
}