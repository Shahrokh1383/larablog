<?php

namespace Modules\Taxonomy\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Taxonomy\Services\CategoryPublicService;
use Modules\Taxonomy\Http\Resources\CategoryPublicResource;
use Modules\Taxonomy\Http\Requests\IndexCategoryPublicRequest;
use Modules\Taxonomy\Http\Requests\ShowCategoryPostsRequest;
use Modules\Articles\Services\Contracts\PostPublicServiceInterface;
use Illuminate\Routing\Controller;

class CategoryPublicController extends Controller
{
    public function __construct(
        private CategoryPublicService $categoryPublicService,
        private PostPublicServiceInterface $postPublicService,
    ) {}

    public function index(IndexCategoryPublicRequest $request): JsonResponse
    {
        $categories = $this->categoryPublicService->getPublicCategories(
            search: $request->validated('search'),
            perPage: $request->validated('per_page', 10)
        );

        return CategoryPublicResource::collection($categories)->response();
    }

    public function posts(string $slug, ShowCategoryPostsRequest $request): JsonResponse
    {
        $category = $this->categoryPublicService->getPublicCategoryBySlug($slug);

        $posts = $this->postPublicService->getPublishedPostsByCategoryForPublic(
            categorySlug: $slug,
            sort: $request->validated('sort', 'newest'),
            perPage: $request->validated('per_page', 10)
        );

        $shapedPosts = $posts->through(fn($post) => $this->shapePublicPost($post));

        return response()->json([
            'category' => new CategoryPublicResource($category),
            'posts'    => $shapedPosts,
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