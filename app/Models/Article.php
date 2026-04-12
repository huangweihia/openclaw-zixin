<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Article extends Model
{
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'summary',
        'content',
        'cover_image',
        'author_id',
        'view_count',
        'like_count',
        'is_vip',
        'is_published',
        'published_at',
        'source_url',
        'meta_keywords',
        'meta_description',
        'svip_subscription_id',
        'is_vip_only',
    ];

    protected $casts = [
        'is_vip' => 'boolean',
        'is_vip_only' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function svipSubscription(): BelongsTo
    {
        return $this->belongsTo(SvipSubscription::class, 'svip_subscription_id');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * 是否可读全文：无门槛文；VIP 文需 VIP/SVIP/管理员；is_vip_only（SVIP 咨询等）需 SVIP/管理员。
     */
    public function userCanReadFull(?User $user): bool
    {
        if (! $this->is_vip && ! $this->is_vip_only) {
            return true;
        }

        if ($user === null || $user->is_banned) {
            return false;
        }

        if ($this->is_vip_only) {
            return $user->canAccessSvipExclusiveContent();
        }

        if ($this->is_vip) {
            return $user->canAccessVipExclusiveContent();
        }

        return true;
    }
}
