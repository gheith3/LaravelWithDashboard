<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'slug'         => $this->slug,
            'content'      => $this->content,
            'header_image' => $this->header_image
                ? Storage::temporaryUrl($this->header_image, now()->addHours(1))
                : null,
            'status'       => $this->status->value,
            'is_active'    => $this->is_active,
            'creator'      => new UserResource($this->whenLoaded('creator')),
            'tags'         => TagResource::collection($this->whenLoaded('tags')),
            'created_at'   => $this->created_at->toIso8601String(),
            'updated_at'   => $this->updated_at->toIso8601String(),
        ];
    }
}
