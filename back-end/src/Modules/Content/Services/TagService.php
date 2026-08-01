<?php

namespace Modules\Content\Services;

use Modules\Content\Models\Tag;
use Modules\Content\Actions\GenerateSlugAction;
use Modules\Content\DTOs\TagCreateDTO;

class TagService
{
    public function __construct(
        private GenerateSlugAction $generateSlugAction
    ) {}

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