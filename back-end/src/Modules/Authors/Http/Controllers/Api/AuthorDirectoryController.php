<?php

namespace Modules\Authors\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AdminStats\Services\Contracts\ContentStatsContract;
use Modules\Articles\Services\Contracts\PostPublicServiceInterface;
use Modules\Profile\Http\Resources\AuthorResource;
use Modules\Profile\Http\Resources\ProfilePostResource;
use Modules\Profile\Http\Resources\ProfileResource;
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

        $allStats = $this->contentStatsService->getAuthorStats();
        
        $profiles->through(function ($profile) use ($allStats) {
            $userId = $profile->user_id;
            $stats = $allStats[$userId] ?? [];
            $profile->posts_count = $stats['posts_count'] ?? 0;
            $profile->total_views = $stats['total_views'] ?? 0;
            return $profile;
        });

        return AuthorResource::collection($profiles)->response();
    }

    public function show(string $username): JsonResponse
    {
        $profile = $this->profileService->getPublicProfileByUsername($username);
        
        if (!$profile) {
            abort(404, 'Profile not found');
        }

        $allStats = $this->contentStatsService->getAuthorStats();
        $stats = $allStats[$profile->user_id] ?? [];
        $profile->posts_count = $stats['posts_count'] ?? 0;
        $profile->total_views = $stats['total_views'] ?? 0;

        return (new ProfileResource($profile))->response();
    }

    public function posts(string $username, Request $request): JsonResponse
    {
        $posts = $this->postService->getPostsByAuthor(
            username: $username,
            sort: $request->input('sort', 'newest'),
            perPage: $request->integer('per_page', 6)
        );

        return ProfilePostResource::collection($posts)->response();
    }
}