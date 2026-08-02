<?php

namespace Modules\Content\Services;

use Modules\Content\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CategoryPublicService
{
    public function getPublicCategories(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return Category::search($search)
            ->withCount([
                'posts as posts_count' => fn($q) => $q->published(),
                'posts as authors_count' => fn($q) => $q->published()->select(DB::raw('count(distinct user_id)'))
            ])
            ->with(['posts' => fn($q) => $q->published()->latest('published_at')->take(4)])
            ->paginate($perPage);
    }
}