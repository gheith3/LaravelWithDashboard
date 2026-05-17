<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Post\StorePostRequest;
use App\Http\Requests\Api\Post\UpdatePostRequest;
use App\Http\Resources\Api\PostResource;
use App\Services\PostService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly PostService $postService) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->postService->list(
            $request->only(['search', 'tag', 'per_page'])
        );

        return $this->paginate(
            PostResource::collection($paginator->items()),
            $paginator,
            __('api.posts.retrieved')
        );
    }

    public function show(string $slug): JsonResponse
    {
        $post = $this->postService->findPublished($slug);

        return $this->success(new PostResource($post), __('api.posts.show'));
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        $post = $this->postService->create(
            auth('api')->user(),
            $request->validated()
        );

        return $this->success(new PostResource($post), __('api.posts.created'), 201);
    }

    public function update(UpdatePostRequest $request, string $slug): JsonResponse
    {
        $post = $this->postService->findByOwner($slug, auth('api')->id());

        $post = $this->postService->update($post, $request->validated());

        return $this->success(new PostResource($post), __('api.posts.updated'));
    }

    public function destroy(string $slug): JsonResponse
    {
        $post = $this->postService->findByOwner($slug, auth('api')->id());

        $this->postService->delete($post);

        return $this->success(message: __('api.posts.deleted'));
    }
}
