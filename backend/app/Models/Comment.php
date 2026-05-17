<?php

namespace App\Models;

use App\Enums\CommentStatus;
use App\Traits\HasUuidWithlog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasUuidWithlog, HasFactory, SoftDeletes;

    protected $fillable = [
        'post_id',
        'name',
        'email',
        'phone',
        'content',
        'status',
    ];

    protected $casts = [
        'status' => CommentStatus::class,
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
