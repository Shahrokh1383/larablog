<?php

namespace Modules\Taxonomy\Http\Controllers\Api;

use Modules\Taxonomy\Models\Tag;
use Modules\Taxonomy\Services\Contracts\TagAdminServiceInterface;
use Modules\Taxonomy\Http\Requests\IndexTagRequest;
use Modules\Taxonomy\Http\Requests\StoreTagRequest;
use Modules\Taxonomy\Http\Requests\UpdateTagRequest;
use Modules\Taxonomy\Http\Resources\TagResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TagController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private TagAdminServiceInterface $tagService
    ) {
        $this->authorizeResource(Tag::class, 'tag');
    }

    public function index(IndexTagRequest $request)
    {
        $perPage = $request->validated('per_page', 15);
        $page = $request->validated('page', 1);

        return TagResource::collection(
            $this->tagService->getAll($perPage, $page)
        );
    }

    public function store(StoreTagRequest $request): TagResource
    {
        return new TagResource(
            $this->tagService->create($request->validated('name'))
        );
    }

    public function show(Tag $tag): TagResource
    {
        return new TagResource(
            $this->tagService->getWithStats($tag)
        );
    }

    public function update(UpdateTagRequest $request, Tag $tag): TagResource
    {
        return new TagResource(
            $this->tagService->update($tag, $request->validated('name'))
        );
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $this->tagService->delete($tag);

        return response()->json(null, 204);
    }
}