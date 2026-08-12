<?php

namespace Modules\About\Http\Controllers\Api;

use Modules\About\Services\SettingsService;
use Modules\About\Services\TeamService;
use Modules\About\Http\Resources\SiteSettingResource;
use Modules\About\Http\Resources\TeamMemberResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class AboutController extends Controller
{
    public function __construct(
        private SettingsService $settingsService,
        private TeamService $teamService,
    ) {}

    public function index(): JsonResponse
    {
        $settings = $this->settingsService->getSettings();
        $members = $this->teamService->getActiveMembers(8);

        return response()->json([
            'data' => [
                'settings' => new SiteSettingResource($settings),
                'team_members' => TeamMemberResource::collection($members->items()),
                'team_members_pagination' => [
                    'current_page' => $members->currentPage(),
                    'last_page' => $members->lastPage(),
                    'per_page' => $members->perPage(),
                    'total' => $members->total(),
                ],
            ],
        ]);
    }
}