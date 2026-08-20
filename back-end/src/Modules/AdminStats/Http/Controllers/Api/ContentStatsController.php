<?php

namespace Modules\AdminStats\Http\Controllers\Api;

use Modules\AdminStats\Services\Contracts\ContentStatsContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class ContentStatsController extends Controller
{
    public function __construct(
        private ContentStatsContract $contentStatsService
    ) {}

    public function dashboard(): JsonResponse
    {
        return response()->json([
            'data' => $this->contentStatsService->getDashboardStats()
        ]);
    }

    public function authors(): JsonResponse
    {
        return response()->json([
            'data' => $this->contentStatsService->getAuthorStats()
        ]);
    }
}