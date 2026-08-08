<?php

namespace Modules\ReaderExperience\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ReaderExperience\Http\Resources\SavedPostResource;
use Modules\ReaderExperience\Services\SavedPostService;

class SavedPostController extends Controller
{
    public function __construct(
        private SavedPostService $savedPostService
    ) {}

    public function toggle(Request $request, string $post): JsonResponse
    {
        $isSaved = $this->savedPostService->toggle($request->user()->id, $post);

        return response()->json(['saved' => $isSaved]);
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $paginated = $this->savedPostService->listPaginated($request->user()->id, $perPage);

        return response()->json([
            'data' => SavedPostResource::collection($paginated->items()),
            'meta' => [
                'total'        => $paginated->total(),
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
            ]
        ]);
    }
}