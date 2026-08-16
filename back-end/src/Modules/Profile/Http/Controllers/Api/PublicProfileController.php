<?php

namespace Modules\Profile\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Taxonomy\Services\Contracts\ContentStatsContract;
use Modules\Articles\Services\PostPublicService;
use Modules\Articles\Http\Resources\PostPublicResource;
use Modules\Profile\Http\Resources\AuthorResource;
use Modules\Profile\Http\Resources\ProfileResource;
use Modules\Profile\Services\Contracts\ProfileServiceInterface;

class PublicProfileController extends Controller
{
    public function __construct(
        private ProfileServiceInterface $profileService
    ) {}

    public function index(Request $request, ContentStatsContract $contentStatsService): JsonResponse
    {
        $profiles = $this->profileService->getAllPublicProfiles(
            search: $request->input('search'),
            perPage: $request->integer('per_page', 12)
        );

        $stats = $contentStatsService->getAuthorStats();

        $profiles->through(function ($profile) use ($stats) {
            $userId = $profile->user_id;
            $profile->posts_count = $stats[$userId]['posts_count'] ?? 0;
            $profile->total_views = $stats[$userId]['total_views'] ?? 0;
            return $profile;
        });

        return AuthorResource::collection($profiles)->response();
    }

    public function show(string $username, ContentStatsContract $contentStatsService): JsonResponse
    {
        $profile = $this->profileService->getPublicProfileByUsername($username);
        
        if (!$profile) {
            abort(404, 'Profile not found');
        }

        // Append stats for the hero section
        $stats = $contentStatsService->getAuthorStats();
        $userId = $profile->user_id;
        $profile->posts_count = $stats[$userId]['posts_count'] ?? 0;
        $profile->total_views = $stats[$userId]['total_views'] ?? 0;

        return (new ProfileResource($profile))->response();
    }

    public function posts(string $username, Request $request, PostPublicService $postPublicService): JsonResponse
    {
        $posts = $postPublicService->getPostsByAuthor(
            username: $username,
            sort: $request->input('sort', 'newest'),
            perPage: $request->integer('per_page', 6)
        );

        return PostPublicResource::collection($posts)->response();
    }
}