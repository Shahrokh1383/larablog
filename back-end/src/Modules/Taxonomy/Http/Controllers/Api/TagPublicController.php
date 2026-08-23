<?php

namespace Modules\Taxonomy\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Taxonomy\Services\TagPublicService;
use Modules\Taxonomy\Http\Resources\TagPublicResource;
use Modules\Taxonomy\Http\Requests\IndexTagPublicRequest;
use Illuminate\Routing\Controller;

class TagPublicController extends Controller
{
    public function __construct(
        private TagPublicService $tagPublicService,
    ) {}

    public function index(IndexTagPublicRequest $request): JsonResponse
    {
        $tags = $this->tagPublicService->getPublicTags(
            search: $request->validated('search'),
            perPage: $request->validated('per_page', 12)
        );

        return TagPublicResource::collection($tags)->response();
    }

    public function popular(): JsonResponse
    {
        $tags = $this->tagPublicService->getPopularTags();

        return response()->json([
            'data' => $tags,
        ]);
    }
}