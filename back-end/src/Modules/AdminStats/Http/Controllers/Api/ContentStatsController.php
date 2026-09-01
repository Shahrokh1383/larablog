<?php

namespace Modules\AdminStats\Http\Controllers\Api;

use Modules\AdminStats\Services\Contracts\ContentStatsContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function me(Request $request): JsonResponse
    {
        $this->authorize('viewAuthorDashboard');

        return response()->json([
            'data' => $this->contentStatsService->getAuthorDashboardStats((string) $request->user()->id)
        ]);
    }

    public function commenters(): JsonResponse
    {
        $this->authorize('viewAdminTopCommenters');

        return response()->json([
            'data' => $this->contentStatsService->getTopCommenters()
        ]);
    }
}