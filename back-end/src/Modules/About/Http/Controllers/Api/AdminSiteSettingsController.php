<?php

namespace Modules\About\Http\Controllers\Api;

use Modules\About\Services\SettingsService;
use Modules\About\DTOs\SiteSettingsDTO;
use Modules\About\Http\Requests\UpdateSiteSettingsRequest;
use Modules\About\Http\Resources\SiteSettingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class AdminSiteSettingsController extends Controller
{
    public function __construct(private SettingsService $settingsService) {}

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
}