<?php

namespace Modules\AdminStats\Http\Controllers\Api;

use Modules\AdminStats\Services\Contracts\ContentStatsContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ContentStatsController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private ContentStatsContract $contentStatsService
    ) {}

    public function dashboard(): JsonResponse
    {
        $this->authorize('viewAdminDashboard');

        return response()->json([
            'data' => $this->contentStatsService->getDashboardStats()
        ]);
    }

    public function authors(): JsonResponse
    {
        $this->authorize('viewAdminAuthors');

        return response()->json([
            'data' => $this->contentStatsService->getAuthorStats()
        ]);
    }
}