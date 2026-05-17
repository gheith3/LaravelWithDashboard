<?php

namespace App\Http\Requests\Api\Post;

use App\Enums\PostStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'        => ['sometimes', 'string', 'min:3', 'max:255'],
            'slug'         => ['sometimes', 'nullable', 'string', 'max:255'],
            'content'      => ['sometimes', 'string'],
            'status'       => ['sometimes', Rule::enum(PostStatus::class)],
            'is_active'    => ['sometimes', 'boolean'],
            'header_image' => ['sometimes', 'nullable', 'image', 'max:5120'],
            'tag_ids'      => ['sometimes', 'nullable', 'array'],
            'tag_ids.*'    => ['string', 'exists:tags,id'],
        ];
    }
}
