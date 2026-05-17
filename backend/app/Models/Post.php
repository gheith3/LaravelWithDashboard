<?php
namespace App\Models;

use App\Enums\PostStatus;
use App\Traits\HasActiveColumn;
use App\Traits\HasUuidWithlog;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasUuidWithlog, HasActiveColumn, HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'created_by_id',
        'header_image',
        'status',
        'is_active',
    ];

    protected $casts = [
        'status'    => PostStatus::class,
        'is_active' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Post $post) {
            if (empty($post->created_by_id)) {
                $post->created_by_id = auth()->id();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    #[Scope]
    public function byStatus(Builder $query, PostStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    #[Scope]
    public function byCreator(Builder $query, string $user_id): Builder
    {
        return $query->where('created_by_id', $user_id);
    }

    #[Scope]
    public function bySlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }
}
