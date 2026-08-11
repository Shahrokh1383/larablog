<?php

namespace Modules\Content\Actions;

use Modules\Content\Models\Post;
use Modules\Profile\Services\Contracts\FetchesPublicProfiles;
use Modules\Engagement\Services\Contracts\CommentServiceInterface;
use Modules\ReaderExperience\Services\Contracts\SavedPostInteractionContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class MapPostRelationsAction
{
    public function __construct(
        private FetchesPublicProfiles $profileService,
        private CommentServiceInterface $commentService,
        private SavedPostInteractionContract $savedPostService,
    ) {}

    public function execute(LengthAwarePaginator|Collection|array $posts): void
    {
        $postsCollection = $posts instanceof LengthAwarePaginator 
            ? collect($posts->items()) 
            : collect($posts);

        if ($postsCollection->isEmpty()) return;

        $this->mapAuthors($postsCollection);
        $this->mapComments($postsCollection);
        $this->mapSavedStatus($postsCollection);
    }

    private function mapAuthors(Collection $posts): void
    {
        $authorIds = $posts->pluck('user_id')->unique()->filter()->values()->toArray();
        if (empty($authorIds)) return;

        $profilesMap = $this->profileService->getPublicProfilesMap($authorIds);
        $posts->each(fn(Post $post) => $post->author = $profilesMap[$post->user_id] ?? null);
    }

    private function mapComments(Collection $posts): void
    {
        $postIds = $posts->pluck('id')->toArray();
        if (empty($postIds)) return;

        $counts = $this->commentService->getCommentCountsForPosts($postIds);
        $posts->each(fn(Post $post) => $post->comments_count = $counts[$post->id] ?? 0);
    }

    private function mapSavedStatus(Collection $posts): void
    {
        $user = Auth::user();
        if (!$user) return;

        $postIds = $posts->pluck('id')->toArray();
        if (empty($postIds)) return;

        $savedIds = $this->savedPostService->getSavedPostIdsForUser($user->id, $postIds);
        $posts->each(fn(Post $post) => $post->is_saved = in_array($post->id, $savedIds));
    }
}