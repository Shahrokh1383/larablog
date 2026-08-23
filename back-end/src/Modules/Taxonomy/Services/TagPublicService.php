<?php

namespace Modules\Taxonomy\Services;

use Modules\Taxonomy\Models\Tag;
use Modules\Taxonomy\Services\Contracts\TagPublicServiceInterface;
use Modules\Articles\Services\Contracts\PostStatsServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TagPublicService implements TagPublicServiceInterface
{
    public function __construct(
        private PostStatsServiceInterface $postStatsService
    ) {}

    public function getPublicTags(?string $search = null, int $perPage = 12): LengthAwarePaginator
    {
        $tags = Tag::search($search)->paginate($perPage);
        $tagIds = $tags->pluck('id')->toArray();

        $postCounts = $this->postStatsService->getPublishedPostCountsByTags($tagIds);

        $tags->each(function ($tag) use ($postCounts) {
            $tag->posts_count = $postCounts[$tag->id] ?? 0;
        });

        return $tags;
    }

    public function getPopularTags(int $limit = 10): array
    {
        $stats = $this->postStatsService->getPopularTagStats($limit);

        if (empty($stats)) {
            return [];
        }

        $tagIds = array_column($stats, 'tag_id');
        $tags = Tag::whereIn('id', $tagIds)->get()->keyBy('id');

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

    public function getTagIdBySlug(string $slug): ?string
    {
        return Tag::where('slug', $slug)->value('id');
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

    public function getTagsByPostIds(array $postIds): array
    {
        if (empty($postIds)) {
            return [];
        }

        $tags = DB::table('content_post_tag as pt')
            ->join('content_tags as t', 't.id', '=', 'pt.tag_id')
            ->whereIn('pt.post_id', $postIds)
            ->select('pt.post_id', 't.id', 't.name', 't.slug')
            ->get();

        $map = [];
        foreach ($tags as $tag) {
            $map[$tag->post_id][] = [
                'id'   => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ];
        }

        return $map;
    }

    public function applyTagPostFilter(Builder $query, string $tagId): Builder
    {
        $postTable = $query->getModel()->getTable();

        return $query->whereExists(function ($subquery) use ($postTable, $tagId) {
            $subquery->select(DB::raw(1))
                ->from('content_post_tag')
                ->whereColumn('content_post_tag.post_id', "{$postTable}.id")
                ->where('content_post_tag.tag_id', $tagId);
        });
    }
}