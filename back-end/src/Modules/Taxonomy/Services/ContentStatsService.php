<?php

namespace Modules\Taxonomy\Services;

use Modules\Taxonomy\Models\Category;
use Modules\Articles\Models\Post;
use Modules\Taxonomy\Models\Tag;
use Modules\Taxonomy\Services\Contracts\ContentStatsContract;

class ContentStatsService implements ContentStatsContract
{
    public function getDashboardStats(): array
    {
        return [
            'total_posts'        => Post::count(),
            'published_posts'    => Post::published()->count(),
            'total_views'        => (int) Post::sum('views'),
            'popular_categories' => Category::withCount('posts')
                ->orderByDesc('posts_count')
                ->take(5)
                ->get(['id', 'name', 'slug'])
                ->toArray(),
            'popular_tags'       => Tag::withCount('posts')
                ->orderByDesc('posts_count')
                ->take(10)
                ->get(['id', 'name', 'slug'])
                ->toArray(),
        ];
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
}