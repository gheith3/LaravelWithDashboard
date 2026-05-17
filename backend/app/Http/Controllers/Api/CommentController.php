<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Comment\StoreCommentRequest;
use App\Http\Resources\Api\CommentResource;
use App\Services\CommentService;
use App\Services\PostService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly PostService    $postService,
        private readonly CommentService $commentService,
    ) {}

    public function index(string $slug): JsonResponse
    {
        $post     = $this->postService->findPublished($slug);
        $comments = $this->commentService->listForPost($post);

        return $this->success(CommentResource::collection($comments), __('api.comments.retrieved'));
    }

    public function store(StoreCommentRequest $request, string $slug): JsonResponse
    {
        $post    = $this->postService->findPublished($slug);
        $comment = $this->commentService->create($post, $request->validated());

        return $this->success(new CommentResource($comment), __('api.comments.submitted'), 201);
    }
}
