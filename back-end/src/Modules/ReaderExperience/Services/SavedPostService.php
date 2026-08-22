<?php

namespace Modules\ReaderExperience\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Articles\Services\Contracts\PostInfoContract;
use Modules\ReaderExperience\Actions\ToggleSavedPostAction;
use Modules\ReaderExperience\Models\SavedPost;
use Modules\ReaderExperience\Services\Contracts\SavedPostInteractionContract;

class SavedPostService implements SavedPostInteractionContract
{
    public function __construct(
        private ToggleSavedPostAction $toggleAction,
        private PostInfoContract $postInfoService
    ) {}

    public function toggle(string $userId, string $postId): bool
    {
        return $this->toggleAction->execute($userId, $postId);
    }

    public function listPaginated(string $userId, int $perPage = 15): LengthAwarePaginator
    {
        $paginator = SavedPost::where('user_id', $userId)
            ->orderBy('saved_at', 'desc')
            ->paginate($perPage);

        $postIds = $paginator->getCollection()->pluck('post_id')->unique()->toArray();
        $postsMap = $this->postInfoService->getPostsByIds($postIds);

        $paginator->getCollection()->transform(function (SavedPost $savedPost) use ($postsMap) {
            $savedPost->post_info = $postsMap[$savedPost->post_id] ?? null;
            return $savedPost;
        });

        return $paginator;
    }

    public function getSavedPostIdsForUser(string $userId, array $postIds): array
    {
        if (empty($postIds)) {
            return [];
        }

        return SavedPost::where('user_id', $userId)
            ->whereIn('post_id', $postIds)
            ->pluck('post_id')
            ->toArray();
    }
}