<?php

namespace Modules\Taxonomy\Services;

use Modules\Taxonomy\Models\Tag;
use Modules\Taxonomy\DTOs\TagCreateDTO;
use Shared\Actions\GenerateSlugAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TagService
{
    public function __construct(
        private GenerateSlugAction $generateSlugAction
    ) {}

    public function getAll(int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return Tag::withCount('posts')
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(TagCreateDTO $dto): Tag
    {
        $slug = $this->generateSlugAction->execute($dto->name, Tag::class);

        return Tag::create([
            'name' => $dto->name,
            'slug' => $slug,
        ]);
    }

    public function update(Tag $tag, string $name): Tag
    {
        if ($name !== $tag->name) {
            $slug = $this->generateSlugAction->execute($name, Tag::class, $tag->id);
            $tag->update(['name' => $name, 'slug' => $slug]);
        }

        return $tag;
    }

    public function delete(Tag $tag): void
    {
        $tag->delete();
    }
}