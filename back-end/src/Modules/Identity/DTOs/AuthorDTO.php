<?php

namespace Modules\Identity\DTOs;

final class AuthorDTO
{
    public function __construct(
        public readonly string|int $id,
        public readonly string $name,
        public readonly string $username,
        public readonly ?string $avatar = null,
        public readonly ?string $bio = null,
    ) {}

    public function toArray(): array
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'username' => $this->username,
            'avatar'   => $this->avatar,
            'bio'      => $this->bio,
        ];
    }
}