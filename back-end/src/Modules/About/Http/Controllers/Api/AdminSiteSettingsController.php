<?php

namespace Modules\About\Http\Controllers\Api;

use Modules\About\Services\SettingsService;
use Modules\About\Actions\UploadStoryImageAction;
use Modules\About\DTOs\SiteSettingsDTO;
use Modules\About\Http\Requests\UpdateSiteSettingsRequest;
use Modules\About\Http\Resources\SiteSettingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdminSiteSettingsController extends Controller
{
    public function __construct(
        private SettingsService $settingsService,
        private UploadStoryImageAction $uploadStoryImageAction
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
            'data' => new SiteSettingResource($settings),
        ]);
    }

    public function uploadStoryImage(Request $request): JsonResponse
    {
        $request->validate([
            'story_image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $url = $this->uploadStoryImageAction->execute($request->file('story_image'));
        return response()->json(['url' => $url]);
    }
}