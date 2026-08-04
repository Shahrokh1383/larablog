<?php

namespace Modules\Administration\Http\Controllers\Api;

use Modules\Administration\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->dashboardService->getStats()
        ]);
    }
}