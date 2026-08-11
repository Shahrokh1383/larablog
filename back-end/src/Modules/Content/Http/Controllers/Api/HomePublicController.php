<?php

namespace Modules\Content\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Content\Services\HomePublicService;
use Modules\Content\Http\Resources\PostPublicResource;
use Modules\Content\Http\Resources\CategoryPublicResource;
use Illuminate\Routing\Controller;

class HomePublicController extends Controller
{
    public function __construct(private HomePublicService $homePublicService) {}

    public function index(): JsonResponse
    {
        $data = $this->homePublicService->getHomeAggregatedData();

        return response()->json([
            'featured_posts'    => PostPublicResource::collection($data['featured_posts']),
            'recent_posts'      => PostPublicResource::collection($data['recent_posts']),
            'categories'        => CategoryPublicResource::collection($data['categories']),
            'total_posts_count' => $data['total_posts_count'],
        ]);
    }
}