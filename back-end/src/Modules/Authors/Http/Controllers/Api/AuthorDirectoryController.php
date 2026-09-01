<?php

namespace Modules\Authors\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AdminStats\Services\Contracts\ContentStatsContract;
use Modules\Articles\Services\Contracts\PostPublicServiceInterface;
use Modules\Authors\Http\Resources\AuthorDirectoryResource;
use Modules\Authors\Http\Resources\AuthorPostResource;
use Modules\Authors\Http\Resources\AuthorProfileResource;
use Modules\Profile\Services\Contracts\FetchesPublicProfiles;

class AuthorDirectoryController extends Controller
{
    public function __construct(
        private FetchesPublicProfiles $profileService,
        private ContentStatsContract $contentStatsService,
        private PostPublicServiceInterface $postService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $profiles = $this->profileService->getAllPublicProfiles(
            search: $request->input('search'),
            perPage: $request->integer('per_page', 12)
        );

        // Optimization: Fetch stats only for the paginated user IDs
        $profileUserIds = $profiles->pluck('user_id')->all();
        $stats = $this->contentStatsService->getAuthorStatsForUserIds($profileUserIds);
        
        $profiles->through(function ($profile) use ($stats) {
            $userId = $profile->user_id;
            $userStats = $stats[$userId] ?? [];
            $profile->posts_count = $userStats['posts_count'] ?? 0;
            $profile->total_views = $userStats['total_views'] ?? 0;
            return $profile;
        });

        return AuthorDirectoryResource::collection($profiles)->response();
    }

    public function show(string $username): JsonResponse
    {
        $profile = $this->profileService->getPublicProfileByUsername($username);
        
        if (!$profile) {
            abort(404, 'Profile not found');
        }

        // Optimization: Fetch stats only for this specific user
        $stats = $this->contentStatsService->getAuthorStatsForUserId($profile->user_id);
        $profile->posts_count = $stats['posts_count'] ?? 0;
        $profile->total_views = $stats['total_views'] ?? 0;

        return (new AuthorProfileResource($profile))->response();
    }

    public function posts(string $username, Request $request): JsonResponse
    {
        $posts = $this->postService->getPostsByAuthor(
            username: $username,
            sort: $request->input('sort', 'newest'),
            perPage: $request->integer('per_page', 6)
        );

        return AuthorPostResource::collection($posts)->response();
    }
}