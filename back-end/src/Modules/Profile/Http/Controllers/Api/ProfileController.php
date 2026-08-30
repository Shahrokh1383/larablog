<?php

namespace Modules\Profile\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Profile\Actions\DeleteAvatarAction;
use Modules\Profile\Actions\UploadAvatarAction;
use Modules\Profile\Http\Requests\UpdateProfileRequest;
use Modules\Profile\Http\Resources\ProfileResource;
use Modules\Profile\Services\Contracts\ProfileServiceInterface;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function __construct(
        private ProfileServiceInterface $profileService,
        private UploadAvatarAction $uploadAvatarAction,
        private DeleteAvatarAction $deleteAvatarAction
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
        // Pass only validated fields as an array to support true partial updates
        $profile = $this->profileService->updateProfile(
            $request->user()->id, 
            $request->validated()
        );

        return (new ProfileResource($profile))->response();
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $url = $this->uploadAvatarAction->execute($request->file('avatar'));
        return response()->json(['url' => $url]);
    }

    public function deleteAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'url' => ['required', 'string', 'url'],
        ]);

        $profile = $this->profileService->getByUserId($request->user()->id);
        
        if (!$profile || $profile->avatar !== $request->input('url')) {
            return response()->json(['message' => 'Avatar not found or unauthorized'], 403);
        }

        $deleted = $this->deleteAvatarAction->execute($profile->avatar);
        
        if ($deleted) {
            $profile->update(['avatar' => null]);
            return response()->json(['message' => 'Avatar deleted successfully']);
        }

        return response()->json(['message' => 'Failed to delete avatar file'], 500);
    }

    public function destroy(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        
        $profile = $this->profileService->getByUserId($userId);
        if ($profile && $profile->avatar) {
            $deleted = $this->deleteAvatarAction->execute($profile->avatar);
            if (!$deleted) {
                Log::error('Failed to delete avatar file during account deletion.', [
                    'user_id' => $userId, 
                    'avatar' => $profile->avatar
                ]);
                return response()->json(['message' => 'Failed to delete avatar. Account deletion aborted.'], 500);
            }
        }

        $this->profileService->deleteAccount($userId);

        return response()->json(null, 204);
    }
}