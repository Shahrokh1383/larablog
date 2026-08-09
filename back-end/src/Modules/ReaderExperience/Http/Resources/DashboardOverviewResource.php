<?php

namespace Modules\ReaderExperience\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardOverviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'posts_read_count'   => $this->resource['posts_read_count'],
            'total_reading_time' => $this->resource['total_reading_time'],
            'comments_count'     => $this->resource['comments_count'],
            'is_top_commenter'   => $this->resource['is_top_commenter'],
            'total_comments'     => $this->resource['total_comments'],
            'total_saved_posts'  => $this->resource['total_saved_posts'],
        ];
    }
}