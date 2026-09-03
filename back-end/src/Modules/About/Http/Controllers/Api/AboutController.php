<?php

namespace Modules\About\Http\Controllers\Api;

use Modules\About\Services\SettingsService;
use Modules\About\Services\TeamService;
use Modules\About\Http\Resources\SiteSettingResource;
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
        $teamData = $this->teamService->getActiveMembersData(TeamService::PUBLIC_TEAM_LIMIT);

        return response()->json([
            'data' => [
                'settings' => new SiteSettingResource($settings),
                'team_members' => $teamData['data'],
                'team_members_pagination' => $teamData['meta'],
            ],
        ]);
    }
}