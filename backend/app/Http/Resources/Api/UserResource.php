<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'avatar_url' => $this->avatar_url
                ? Storage::temporaryUrl($this->avatar_url, now()->addMinutes(30))
                : null,
            'is_active'  => $this->is_active,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
