<?php

namespace Modules\Taxonomy\Services;

use Modules\Taxonomy\Models\Tag;
use Modules\Articles\Services\Contracts\PostAdminStatsServiceInterface;
use Modules\Taxonomy\Services\Contracts\TagAdminServiceInterface;
use Shared\Actions\GenerateSlugAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TagService implements TagAdminServiceInterface
{
    public function __construct(
        private GenerateSlugAction $generateSlugAction,
        private PostAdminStatsServiceInterface $postAdminStatsService
    ) {}

    public function getAll(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $tags = Tag::orderBy('name')->paginate($perPage, ['*'], 'page', $page);
        $tagIds = $tags->pluck('id')->toArray();

        $counts = $this->postAdminStatsService->getTotalPostCountsByTags($tagIds);

        $tags->each(function ($tag) use ($counts) {
            $tag->posts_count = $counts[$tag->id] ?? 0;
        });

        return $tags;
    }

    public function getWithStats(Tag $tag): Tag
    {
        $counts = $this->postAdminStatsService->getTotalPostCountsByTags([$tag->id]);
        $tag->posts_count = $counts[$tag->id] ?? 0;

        return $tag;
    }

    public function create(string $name): Tag
    {
        $slug = $this->generateSlugAction->execute($name, Tag::class);

        return Tag::create([
            'name' => $name,
            'slug' => (string) $slug,
        ]);
    }

    public function update(Tag $tag, string $name): Tag
    {
        if ($name !== $tag->name) {
            $slug = $this->generateSlugAction->execute($name, Tag::class, $tag->id);
            $tag->update(['name' => $name, 'slug' => (string) $slug]);
        }

        return $this->getWithStats($tag);
    }

    public function delete(Tag $tag): void
    {
        $tag->delete();
    }

    public function getByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        return Tag::whereIn('id', $ids)
            ->get(['id', 'name', 'slug'])
            ->mapWithKeys(fn($tag) => [
                $tag->id => [
                    'id'   => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ]
            ])
            ->toArray();
    }
}