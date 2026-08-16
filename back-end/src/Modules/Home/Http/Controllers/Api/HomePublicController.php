<?php

namespace Modules\Home\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Home\Services\HomePublicService;
use Illuminate\Routing\Controller;

class HomePublicController extends Controller
{
    public function __construct(
        private HomePublicService $homePublicService
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(
            $this->homePublicService->getHomeAggregatedData()
        );
    }
}