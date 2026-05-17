<?php

namespace App\Services;

use App\Enums\FileStoragePath;
use App\Enums\PostStatus;
use App\Exceptions\ServiceException;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        try {
            $query = Post::query()
                ->byStatus(PostStatus::Published)
                ->active()
                ->with(['creator:id,name,email,avatar_url,is_active,created_at', 'tags:id,name,slug'])
                ->latest();

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            }

            if (!empty($filters['tag'])) {
                $query->whereHas('tags', fn($q) => $q->where('slug', $filters['tag'])->where('is_active', true));
            }

            $perPage = min((int) ($filters['per_page'] ?? 10), 50);

            return $query->paginate($perPage);
        } catch (\Throwable $e) {
            Log::error('PostService::list failed', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'filters' => $filters,
            ]);

            throw new ServiceException(__('api.errors.server'));
        }
    }

    // Not wrapped — ModelNotFoundException is the expected 404 flow
    public function findPublished(string $slug): Post
    {
        return Post::query()
            ->bySlug($slug)
            ->byStatus(PostStatus::Published)
            ->active()
            ->with(['creator:id,name,email,avatar_url,is_active,created_at', 'tags:id,name,slug'])
            ->firstOrFail();
    }

    // Not wrapped — ModelNotFoundException is the expected 404 flow
    public function findByOwner(string $slug, string $userId): Post
    {
        return Post::query()
            ->bySlug($slug)
            ->byCreator($userId)
            ->firstOrFail();
    }

    public function create(User $user, array $data): Post
    {
        try {
            $data['header_image'] = $this->storeImage($data['header_image'] ?? null);
            $data['slug'] = $this->uniqueSlug($data['title'], $data['slug'] ?? null);
            $data['created_by_id'] = $user->id;
            $data['status'] ??= PostStatus::Draft;

            $tagIds = $data['tag_ids'] ?? [];
            unset($data['tag_ids']);

            $post = Post::create($data);

            if (!empty($tagIds)) {
                $post->tags()->sync($tagIds);
            }

            return $post->load(['creator:id,name,email,avatar_url,is_active,created_at', 'tags:id,name,slug']);
        } catch (\Throwable $e) {
            Log::error('PostService::create failed', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => $user->id,
                'post_title' => $data['title'] ?? null,
            ]);

            throw new ServiceException(__('api.errors.server'));
        }
    }

    public function update(Post $post, array $data): Post
    {
        try {
            if (isset($data['header_image'])) {
                $this->deleteImage($post->header_image);
                $data['header_image'] = $this->storeImage($data['header_image']);
            }

            if (isset($data['title']) && !isset($data['slug'])) {
                $data['slug'] = $this->uniqueSlug($data['title'], null, $post->id);
            } elseif (isset($data['slug'])) {
                $data['slug'] = $this->uniqueSlug($data['title'] ?? $post->title, $data['slug'], $post->id);
            }

            $tagIds = $data['tag_ids'] ?? null;
            unset($data['tag_ids']);

            $post->update($data);

            if ($tagIds !== null) {
                $post->tags()->sync($tagIds);
            }

            return $post->fresh(['creator:id,name,email,avatar_url,is_active,created_at', 'tags:id,name,slug']);
        } catch (\Throwable $e) {
            Log::error('PostService::update failed', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'post_id' => $post->id,
                'post_slug' => $post->slug,
            ]);

            throw new ServiceException(__('api.errors.server'));
        }
    }

    public function delete(Post $post): void
    {
        try {
            $this->deleteImage($post->header_image);
            $post->delete();
        } catch (\Throwable $e) {
            Log::error('PostService::delete failed', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'post_id' => $post->id,
                'post_slug' => $post->slug,
            ]);

            throw new ServiceException(__('api.errors.server'));
        }
    }

    private function storeImage(?UploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }

        return Storage::put(FileStoragePath::PostHeaderImage->value, $file);
    }

    private function deleteImage(?string $path): void
    {
        if ($path) {
            Storage::delete($path);
        }
    }

    private function uniqueSlug(string $title, ?string $slug = null, ?string $excludeId = null): string
    {
        $base = Str::slug($slug ?? $title);
        $candidate = $base;
        $i = 1;

        while (
            Post::where('slug', $candidate)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $candidate = "{$base}-{$i}";
            $i++;
        }

        return $candidate;
    }
}
