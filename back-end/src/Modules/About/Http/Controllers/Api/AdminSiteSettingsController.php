<?php

namespace Modules\About\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\About\DTOs\SiteSettingsDTO;
use Modules\About\Http\Requests\DeleteStoryImageRequest;
use Modules\About\Http\Requests\UpdateSiteSettingsRequest;
use Modules\About\Http\Requests\UploadStoryImageRequest;
use Modules\About\Http\Resources\SiteSettingResource;
use Modules\About\Services\SettingsService;

class AdminSiteSettingsController extends Controller
{
    public function __construct(
        private SettingsService $settingsService,
    ) {}

    public function show(): JsonResponse
    {
        return response()->json([
            'data' => new SiteSettingResource($this->settingsService->getSettings()),
        ]);
    }

    public function update(UpdateSiteSettingsRequest $request): JsonResponse
    {
        $dto = SiteSettingsDTO::fromRequest($request->validated());
        $settings = $this->settingsService->updateSettings($dto);

        return response()->json([
            'message' => 'Site settings updated successfully.',
            'data'    => new SiteSettingResource($settings),
        ]);
    }

    public function uploadStoryImage(UploadStoryImageRequest $request): JsonResponse
    {
        $url = $this->settingsService->uploadStoryImage($request->image());

        return response()->json(['url' => $url]);
    }

    public function deleteStoryImage(DeleteStoryImageRequest $request): JsonResponse
    {
        $this->settingsService->deleteStoryImage($request->imageUrl());

        return response()->json(['message' => 'Story image deleted successfully.']);
    }
}