<?php

namespace Modules\Content\DTOs;

final class TagCreateDTO
{
    public function __construct(
        public readonly string $name,
    ) {}
}