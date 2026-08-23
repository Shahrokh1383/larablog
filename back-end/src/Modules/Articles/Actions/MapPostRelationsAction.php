<?php

namespace Modules\Articles\Actions;

use Modules\Articles\Models\Post;
use Modules\Profile\Services\Contracts\FetchesPublicProfiles;
use Modules\Engagement\Services\Contracts\CommentServiceInterface;
use Modules\ReaderExperience\Services\Contracts\SavedPostInteractionContract;
use Modules\Taxonomy\Services\Contracts\CategoryPublicServiceInterface;
use Modules\Taxonomy\Services\Contracts\TagPublicServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class MapPostRelationsAction
{
    private const ALL_RELATIONS = ['authors', 'comments', 'savedStatus', 'categories', 'tags'];

    public function __construct(
        private FetchesPublicProfiles $profileService,
        private CommentServiceInterface $commentService,
        private SavedPostInteractionContract $savedPostService,
        private CategoryPublicServiceInterface $categoryService,
        private TagPublicServiceInterface $tagService,
    ) {}

    public function execute(LengthAwarePaginator|Collection|array $posts, array $relations = self::ALL_RELATIONS): void
    {
        $postsCollection = $posts instanceof LengthAwarePaginator 
            ? collect($posts->items()) 
            : collect($posts);

        if ($postsCollection->isEmpty()) return;

        if (in_array('authors', $relations, true)) $this->mapAuthors($postsCollection);
        if (in_array('comments', $relations, true)) $this->mapComments($postsCollection);
        if (in_array('savedStatus', $relations, true)) $this->mapSavedStatus($postsCollection);
        if (in_array('categories', $relations, true)) $this->mapCategories($postsCollection);
        if (in_array('tags', $relations, true)) $this->mapTags($postsCollection);
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

    private function mapCategories(Collection $posts): void
    {
        $categoryIds = $posts->pluck('category_id')->unique()->filter()->values()->toArray();
        if (empty($categoryIds)) return;

        $categoriesMap = $this->categoryService->getCategoriesByIds($categoryIds);
        $posts->each(function (Post $post) use ($categoriesMap) {
            $post->category_detail = $categoriesMap[$post->category_id] ?? null;
        });
    }

    private function mapTags(Collection $posts): void
    {
        $postIds = $posts->pluck('id')->toArray();
        if (empty($postIds)) return;

        $tagsMap = $this->tagService->getTagsByPostIds($postIds);
        $posts->each(function (Post $post) use ($tagsMap) {
            $post->tags_detail = $tagsMap[$post->id] ?? [];
        });
    }
}