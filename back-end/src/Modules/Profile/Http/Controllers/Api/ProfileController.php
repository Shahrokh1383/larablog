<?php

namespace Modules\Profile\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Profile\DTOs\UpdateProfileDTO;
use Modules\Profile\Http\Requests\UpdateProfileRequest;
use Modules\Profile\Http\Resources\ProfileResource;
use Modules\Profile\Services\Contracts\ProfileServiceInterface;

class ProfileController extends Controller
{
    public function __construct(
        private ProfileServiceInterface $profileService
    ) {}

    public function show(Request $request): JsonResponse
    {
        $profile = $this->profileService->getByUserId($request->user()->id);
        
        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        return (new ProfileResource($profile))->response();
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $dto = new UpdateProfileDTO(
            name:                 $request->validated('name'),
            avatar:               $request->validated('avatar'),
            bio:                  $request->validated('bio'),
            expertise:            $request->validated('expertise'),
            years_of_experience:  $request->validated('years_of_experience'),
            social_links:         $request->validated('social_links'),
        );

        $profile = $this->profileService->updateProfile($request->user()->id, $dto);

        return (new ProfileResource($profile))->response();
    }
}