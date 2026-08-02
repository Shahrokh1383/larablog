<?php

namespace Modules\Identity\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Identity\Services\Contracts\AuthorServiceInterface;
use Modules\Identity\Http\Resources\AuthorResource;
use Illuminate\Routing\Controller;

class AuthorController extends Controller
{
    public function __construct(
        private AuthorServiceInterface $authorService
    ) {}

    public function index(): JsonResponse
    {
        $authors = $this->authorService->getAllAuthors();
        return AuthorResource::collection(collect($authors))->response();
    }

    public function show(string $username): JsonResponse
    {
        $author = $this->authorService->getByUsername($username);
        if (!$author) {
            abort(404, 'Author not found');
        }
        return (new AuthorResource($author))->response();
    }
}