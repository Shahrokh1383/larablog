<?php

namespace Modules\Taxonomy\Services;

use Modules\Taxonomy\Models\Tag;
use Modules\Taxonomy\Services\Contracts\TagPublicServiceInterface;
use Modules\Articles\Services\Contracts\PostPublicServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TagPublicService implements TagPublicServiceInterface
{
    public function __construct(
        private PostPublicServiceInterface $postPublicService
    ) {}

    public function getPublicTags(?string $search = null, int $perPage = 12): LengthAwarePaginator
    {
        $tags = Tag::search($search)->paginate($perPage);
        $tagIds = $tags->pluck('id')->toArray();

        $postCounts = $this->postPublicService->getPublishedPostCountsByTags($tagIds);

        $tags->each(function ($tag) use ($postCounts) {
            $tag->posts_count = $postCounts[$tag->id] ?? 0;
        });

        return $tags;
    }

    public function getPopularTags(int $limit = 10): array
    {
        // 1. Ask Articles for the top tag IDs, post counts, and view sums
        $stats = $this->postPublicService->getPopularTagStats($limit);
        if (empty($stats)) return [];

        // 2. Fetch the actual Tag models by those IDs
        $tagIds = array_column($stats, 'tag_id');
        $tags = Tag::whereIn('id', $tagIds)->get()->keyBy('id');

        // 3. Map and preserve the exact order returned by Articles
        $result = [];
        foreach ($stats as $stat) {
            $tag = $tags[$stat['tag_id']] ?? null;
            if ($tag) {
                $result[] = [
                    'id'          => $tag->id,
                    'name'        => $tag->name,
                    'slug'        => $tag->slug,
                    'posts_count' => (int) $stat['posts_count'],
                    'total_views' => (int) $stat['total_views'],
                ];
            }
        }

        return $result;
    }

    public function getPublicTagBySlug(string $slug): Tag
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();
        
        $postCounts = $this->postPublicService->getPublishedPostCountsByTags([$tag->id]);
        $tag->posts_count = $postCounts[$tag->id] ?? 0;

        return $tag;
    }

    public function getTagIdBySlug(string $slug): string
    {
        return Tag::where('slug', $slug)->firstOrFail()->id;
    }

    public function getPopularTagsAsArray(int $limit = 10): array
    {
        return $this->getPopularTags($limit);
    }

    public function getTagStats(): array
    {
        return [
            'total_tags' => Tag::count(),
        ];
    }
}