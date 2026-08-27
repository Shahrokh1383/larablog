<?php

namespace Modules\Articles\Services;

use Modules\Articles\Models\Post;
use Modules\Articles\Services\Contracts\PostAdminStatsServiceInterface;
use Illuminate\Support\Facades\DB;

class PostAdminStatsService implements PostAdminStatsServiceInterface
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

    public function getTotalPostCountsByCategories(array $categoryIds): array
    {
        if (empty($categoryIds)) {
            return [];
        }
        
        return Post::select('category_id')
            ->selectRaw('count(*) as count')
            ->whereIn('category_id', $categoryIds)
            ->groupBy('category_id')
            ->pluck('count', 'category_id')
            ->toArray();
    }

    public function getTotalPostCountsByTags(array $tagIds): array
    {
        if (empty($tagIds)) {
            return [];
        }
        
        return DB::table('content_post_tag')
            ->select('tag_id')
            ->selectRaw('count(*) as count')
            ->whereIn('tag_id', $tagIds)
            ->groupBy('tag_id')
            ->pluck('count', 'tag_id')
            ->toArray();
    }

    public function getPopularCategoryStats(int $limit): array
    {
        return Post::select('category_id')
            ->selectRaw('count(*) as posts_count')
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

    public function getPopularTagStats(int $limit): array
    {
        return DB::table('content_post_tag')
            ->select('tag_id')
            ->selectRaw('count(*) as posts_count')
            ->groupBy('tag_id')
            ->orderByDesc('posts_count')
            ->limit($limit)
            ->get()
            ->map(fn($row) => [
                'tag_id'      => $row->tag_id,
                'posts_count' => (int) $row->posts_count,
            ])
            ->toArray();
    }
}