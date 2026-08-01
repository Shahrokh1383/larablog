<?php

namespace Modules\Content\DTOs;

final class CategoryCreateDTO
{
    public function __construct(
        public readonly string $name,
    ) {}
}