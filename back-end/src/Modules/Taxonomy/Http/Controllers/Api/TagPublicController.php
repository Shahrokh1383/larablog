<?php

namespace Modules\Taxonomy\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Taxonomy\Services\TagPublicService;
use Modules\Taxonomy\Http\Resources\TagPublicResource;
use Modules\Taxonomy\Http\Requests\IndexTagPublicRequest;
use Modules\Taxonomy\Http\Requests\ShowTagPostsRequest;
use Modules\Articles\Services\Contracts\PostPublicServiceInterface;
use Illuminate\Routing\Controller;

class TagPublicController extends Controller
{
    public function __construct(
        private TagPublicService $tagPublicService,
        private PostPublicServiceInterface $postPublicService,
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

    public function posts(string $slug, ShowTagPostsRequest $request): JsonResponse
    {
        $tag = $this->tagPublicService->getPublicTagBySlug($slug);

        $posts = $this->postPublicService->getPublishedPostsByTagForPublic(
            tagSlug: $slug,
            sort: $request->validated('sort', 'newest'),
            perPage: $request->validated('per_page', 10)
        );

        $shapedPosts = $posts->through(fn($post) => $this->shapePublicPost($post));

        return response()->json([
            'tag'   => new TagPublicResource($tag),
            'posts' => $shapedPosts,
        ]);
    }

    private function shapePublicPost(object $post): array
    {
        return [
            'id'              => $post->id,
            'title'           => $post->title,
            'slug'            => $post->slug,
            'body'            => $post->body,
            'excerpt'         => $post->excerpt,
            'featured_image'  => $post->featured_image,
            'reading_time'    => $post->reading_time,
            'views'           => $post->views,
            'comments_count'  => $post->comments_count ?? 0,
            'is_saved'        => $post->is_saved ?? false,
            'published_at'    => $post->published_at,
            'is_editors_pick' => $post->is_editors_pick,
            'category'        => $post->category_detail ?? null,
            'tags'            => $post->tags_detail ?? [],
            'author'          => $post->author ?? null,
            'created_at'      => $post->created_at,
            'updated_at'      => $post->updated_at,
        ];
    }
}