<?php

namespace Shared\ValueObjects;

use InvalidArgumentException;

final class Slug
{
    public function __construct(
        public readonly string $value
    ) {
        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value)) {
            throw new InvalidArgumentException("Invalid slug: {$value}");
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function fromString(string $input): self
    {
        $slug = strtolower(trim($input));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        if (empty($slug)) {
            throw new InvalidArgumentException("Slug cannot be empty after sanitization.");
        }
        return new self($slug);
    }
}