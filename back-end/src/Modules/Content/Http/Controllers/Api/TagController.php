<?php

namespace Modules\Content\Http\Controllers\Api;

use Modules\Content\Models\Tag;
use Modules\Content\Services\TagService;
use Modules\Content\Http\Requests\StoreTagRequest;
use Modules\Content\Http\Requests\UpdateTagRequest;
use Modules\Content\Http\Resources\TagResource;
use Modules\Content\DTOs\TagCreateDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class TagController extends Controller
{
    public function __construct(
        private TagService $tagService
    ) {
        $this->authorizeResource(Tag::class, 'tag');
    }

    public function index()
    {
        $tags = Tag::orderBy('name')->get();
        return TagResource::collection($tags);
    }

    public function store(StoreTagRequest $request): TagResource
    {
        $dto = new TagCreateDTO(name: $request->validated('name'));
        $tag = $this->tagService->create($dto);
        return new TagResource($tag);
    }

    public function show(Tag $tag): TagResource
    {
        return new TagResource($tag);
    }

    public function update(UpdateTagRequest $request, Tag $tag): TagResource
    {
        $tag = $this->tagService->update($tag, $request->validated('name'));
        return new TagResource($tag);
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $this->tagService->delete($tag);
        return response()->json(null, 204);
    }
}