<?php

namespace Modules\ReaderExperience\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ReaderExperience\Http\Resources\DashboardOverviewResource;
use Modules\ReaderExperience\Http\Resources\RecentlyReadResource;
use Modules\ReaderExperience\Services\DashboardService;
use Modules\ReaderExperience\Http\Resources\UserCommentResource;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function overview(Request $request): JsonResponse
    {
        $data = $this->dashboardService->getOverview($request->user()->id);

        return (new DashboardOverviewResource($data))
            ->response()
            ->setStatusCode(200);
    }

    public function recentlyRead(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $paginated = $this->dashboardService->getRecentlyRead($request->user()->id, $perPage);

        return response()->json([
            'data' => RecentlyReadResource::collection($paginated->items()),
            'meta' => [
                'total'        => $paginated->total(),
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
            ]
        ]);
    }

    public function comments(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $paginated = $this->dashboardService->getUserCommentsPaginated($request->user()->id, $perPage);

        return response()->json([
            'data' => UserCommentResource::collection($paginated->items()),
            'meta' => [
                'total'        => $paginated->total(),
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
            ]
        ]);
    }
}