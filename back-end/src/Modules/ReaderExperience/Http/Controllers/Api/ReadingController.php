<?php

namespace Modules\ReaderExperience\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ReaderExperience\Actions\TrackPostReadAction;

class ReadingController extends Controller
{
    public function __construct(
        private TrackPostReadAction $trackPostReadAction
    ) {}

    public function store(Request $request, string $post): JsonResponse
    {
        $this->trackPostReadAction->execute($request->user()->id, $post);

        return response()->json(null, 204);
    }
}