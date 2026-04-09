<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    protected $fillable = [
        'user_id',
        'commentable_type',
        'commentable_id',
        'parent_id',
        'content',
        'is_hidden',
        'like_count',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }
    
    public function replies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->latest();
    }

    /**
     * 讨论串根评论（parent_id 为 null 的祖先）。
     */
    public function threadRoot(): self
    {
        $c = $this;
        $guard = 0;
        while ($c->parent_id !== null && $guard < 50) {
            $c = $c->relationLoaded('parent') ? $c->parent : $c->parent()->firstOrFail();
            $guard++;
        }

        return $c;
    }
}
