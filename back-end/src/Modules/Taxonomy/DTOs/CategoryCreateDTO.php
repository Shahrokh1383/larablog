<?php

namespace Modules\Taxonomy\DTOs;

final class CategoryCreateDTO
{
    public function __construct(
        public readonly string $name,
    ) {}
}