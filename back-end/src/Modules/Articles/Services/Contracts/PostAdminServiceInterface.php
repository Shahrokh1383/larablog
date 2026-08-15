<?php

namespace Modules\Articles\Services\Contracts;

use Modules\Articles\Models\Post;

interface PostAdminServiceInterface
{
    public function find(string $id): ?Post;
}