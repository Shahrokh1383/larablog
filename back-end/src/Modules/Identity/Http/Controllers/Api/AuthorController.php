<?php

namespace Modules\Identity\Http\Controllers\Api;

use Modules\Identity\Http\Resources\AuthorResource;
use Modules\Identity\Services\Contracts\AuthorServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class AuthorController extends Controller
{
    public function __construct(protected AuthorServiceInterface $authorService) {}

    public function show(string $username): JsonResponse
    {
        $authorDTO = $this->authorService->findByUsername($username);

        if (! $authorDTO) {
            return response()->json(['message' => 'Author not found'], 404);
        }

        return response()->json(new AuthorResource($authorDTO));
    }

    public function index(): JsonResponse
    {
        $authors = $this->authorService->listAuthors();

        return response()->json(AuthorResource::collection($authors));
    }
}