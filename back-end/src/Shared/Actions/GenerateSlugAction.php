<?php

namespace Shared\Actions;

use Shared\ValueObjects\Slug;
use Illuminate\Database\Eloquent\Model;

class GenerateSlugAction
{
    /**
     * @param string $title
     * @param class-string<Model> $modelClass e.g. Post::class
     * @param string|null $excludeId UUID to ignore for uniqueness
     * @return Slug
     */
    public function execute(string $title, string $modelClass, ?string $excludeId = null): Slug
    {
        $slug = Slug::fromString($title);
        $original = (string) $slug;
        $counter = 1;

        while ($this->slugExists($modelClass, (string) $slug, $excludeId)) {
            $slug = new Slug($original . '-' . $counter);
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $modelClass, string $slug, ?string $excludeId): bool
    {
        $query = $modelClass::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }
}