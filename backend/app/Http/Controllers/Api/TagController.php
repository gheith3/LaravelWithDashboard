<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\TagResource;
use App\Models\Tag;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $tags = Tag::where('is_active', true)
            ->orderBy('name')
            ->get();

        return $this->success(TagResource::collection($tags), __('api.tags.retrieved'));
    }
}
