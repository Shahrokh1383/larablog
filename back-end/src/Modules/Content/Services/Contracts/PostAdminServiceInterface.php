<?php

namespace Modules\Content\Services\Contracts;

use Modules\Content\Models\Post;

interface PostAdminServiceInterface
{
    public function find(string $id): ?Post;
}