<?php

namespace Modules\Articles\Services;

use Modules\Articles\Models\Post;
use Modules\Articles\Services\Contracts\PostInfoContract;
use Carbon\Carbon;

class PostInfoService implements PostInfoContract
{
    public function getPostInfo(string $postId): ?object
    {
        $post = Post::find($postId);
        if (!$post) return null;
        
        return (object) [
            'authorId' => $post->user_id,
            'title'    => $post->title,
            'slug'     => $post->slug,
        ];
    }

    public function getPostReadingTime(string $postId): ?object
    {
        $post = Post::find($postId);
        if (!$post) return null;

        return (object) [
            'readingTime' => $post->reading_time ?? 0,
        ];
    }

    public function getPostsByIds(array $postIds): array
    {
        if (empty($postIds)) return [];

        $posts = Post::select(['id', 'title', 'slug', 'featured_image', 'reading_time', 'user_id'])
            ->whereIn('id', $postIds)
            ->get();

        return $posts->mapWithKeys(function (Post $post) {
            return [
                $post->id => (object) [
                    'id'             => $post->id,
                    'title'          => $post->title,
                    'slug'           => $post->slug,
                    'reading_time'   => $post->reading_time ?? 0,
                    'featured_image' => $post->featured_image,
                    'author_id'      => $post->user_id,
                ]
            ];
        })->all();
    }

    public function getTotalReadingTimeByIds($postIds): int
    {
        if (is_array($postIds) && empty($postIds)) {
            return 0;
        }

        return (int) Post::whereIn('id', $postIds)->sum('reading_time');
    }

    public function getTopPostsOfWeek(int $limit = 5): array
    {
        return Post::published()
            ->where('published_at', '>=', Carbon::now()->subWeek())
            ->orderByDesc('views')
            ->limit($limit)
            ->get(['id', 'title', 'slug', 'excerpt', 'views'])
            ->map(fn(Post $post) => (object)[
                'id'      => $post->id,
                'title'   => $post->title,
                'slug'    => $post->slug,
                'excerpt' => $post->excerpt,
                'views'   => $post->views,
            ])->all();
    }
}