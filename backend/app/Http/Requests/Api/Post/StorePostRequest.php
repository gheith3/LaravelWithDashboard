<?php

namespace App\Http\Requests\Api\Post;

use App\Enums\PostStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'min:3', 'max:255'],
            'slug'         => ['nullable', 'string', 'max:255'],
            'content'      => ['required', 'string'],
            'status'       => ['nullable', Rule::enum(PostStatus::class)],
            'is_active'    => ['nullable', 'boolean'],
            'header_image' => ['nullable', 'image', 'max:5120'],
            'tag_ids'      => ['nullable', 'array'],
            'tag_ids.*'    => ['string', 'exists:tags,id'],
        ];
    }
}
