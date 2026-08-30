<?php

namespace Modules\Profile\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Articles\Services\Contracts\PostPublicServiceInterface;
use Modules\Profile\Http\Resources\AuthorResource;
use Modules\Profile\Http\Resources\ProfilePostResource;
use Modules\Profile\Http\Resources\ProfileResource;
use Modules\Profile\Services\Contracts\ProfileServiceInterface;

class PublicProfileController extends Controller
{
    public function __construct(
        private ProfileServiceInterface $profileService
    ) {}

    public function index(Request $request): JsonResponse
    {
        // Delegated aggregation logic to the service layer
        $profiles = $this->profileService->getPublicProfilesWithStats(
            search: $request->input('search'),
            perPage: $request->integer('per_page', 12)
        );

        return AuthorResource::collection($profiles)->response();
    }

    public function show(string $username): JsonResponse
    {
        // Delegated aggregation logic to the service layer
        $profile = $this->profileService->getPublicProfileWithStats($username);
        
        if (!$profile) {
            abort(404, 'Profile not found');
        }

        return (new ProfileResource($profile))->response();
    }

    public function posts(string $username, Request $request, PostPublicServiceInterface $postPublicService): JsonResponse
    {
        $posts = $postPublicService->getPostsByAuthor(
            username: $username,
            sort: $request->input('sort', 'newest'),
            perPage: $request->integer('per_page', 6)
        );

        return ProfilePostResource::collection($posts)->response();
    }
}