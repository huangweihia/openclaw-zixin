<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class UserPost extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'content',
        'category',
        'tags',
        'cover_image',
        'attachments',
        'visibility',
        'status',
        'audit_note',
        'audited_by',
        'audited_at',
        'view_count',
        'like_count',
        'comment_count',
        'favorite_count',
        'heat_score',
        'boost_weight',
        'last_boost_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'attachments' => 'array',
        'audited_at' => 'datetime',
        'last_boost_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'audited_by');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * 前台列表：仅已通过且对访客可见（公开或 VIP 专享列表展示，详情再校验会员）。
     */
    public function scopePublicFeed(Builder $query): Builder
    {
        return $query->where('status', 'approved')
            ->whereIn('visibility', ['public', 'vip']);
    }
}
