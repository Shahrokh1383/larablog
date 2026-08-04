<?php

namespace Modules\Profile\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Profile\Http\Resources\ProfileResource;
use Modules\Profile\Services\Contracts\ProfileServiceInterface;

class PublicProfileController extends Controller
{
    public function __construct(
        private ProfileServiceInterface $profileService
    ) {}

    public function show(string $username): JsonResponse
    {
        $profile = $this->profileService->getPublicProfileByUsername($username);
        
        if (!$profile) {
            abort(404, 'Profile not found');
        }

        return (new ProfileResource($profile))->response();
    }
}