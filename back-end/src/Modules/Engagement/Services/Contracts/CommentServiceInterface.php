<?php

namespace Modules\Engagement\Services\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CommentServiceInterface
{
    public function getCommentCountsForPosts(array $postIds): array;
    
    /**
     * Get paginated comments for a user, enriched with post info via Content Service.
     */
    public function getUserCommentsPaginated(string $userId, int $perPage = 15): LengthAwarePaginator;
    
    /**
     * Get top commenters for the current week.
     * @return Collection<int, object{user_id: string, comments_count: int}>
     */
    public function getWeeklyTopCommenters(int $limit = 10): Collection;

    /**
     * Get the total number of comments made by a user this week.
     */
    public function getWeeklyCommentCountForUser(string $userId): int;

    /**
     * Get the TOTAL number of comments made by a user (all time).
     */
    public function getTotalCommentCountForUser(string $userId): int;
}