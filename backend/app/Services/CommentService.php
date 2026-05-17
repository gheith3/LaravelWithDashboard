<?php

namespace App\Services;

use App\Enums\CommentStatus;
use App\Exceptions\ServiceException;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class CommentService
{
    public function listForPost(Post $post): Collection
    {
        try {
            return $post->comments()
                ->where('status', CommentStatus::Accepted)
                ->latest()
                ->get();
        } catch (\Throwable $e) {
            Log::error('CommentService::listForPost failed', [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'post_id'   => $post->id,
            ]);

            throw new ServiceException(__('api.errors.server'));
        }
    }

    public function create(Post $post, array $data): Comment
    {
        try {
            return Comment::create([
                ...$data,
                'post_id' => $post->id,
                'status'  => CommentStatus::Review,
            ]);
        } catch (\Throwable $e) {
            Log::error('CommentService::create failed', [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'post_id'   => $post->id,
            ]);

            throw new ServiceException(__('api.errors.server'));
        }
    }
}
