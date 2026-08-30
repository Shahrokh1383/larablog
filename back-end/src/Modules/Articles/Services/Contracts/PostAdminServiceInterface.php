<?php

namespace Modules\Articles\Services\Contracts;

use Modules\Articles\Models\Post;
use Modules\Articles\DTOs\PostCreateDTO;
use Modules\Articles\DTOs\PostUpdateDTO;
use Shared\Contracts\HasRolesContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

interface PostAdminServiceInterface
{
    public function getAll(?string $search, ?HasRolesContract $user, int $perPage, int $page, ?bool $isEditorPick): LengthAwarePaginator;
    public function create(PostCreateDTO $dto): Post;
    public function update(Post $post, PostUpdateDTO $dto): Post;
    public function delete(Post $post): void;
    public function find(string $id): ?Post;
    public function findViewablePostId(string $id, HasRolesContract $user): ?string;
    public function enrich(Post $post): Post;
    public function uploadImage(UploadedFile $file): string;
    public function deleteImage(string $url): bool;
}