<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Project extends Model
{
    protected $fillable = [
        'name',
        'full_name',
        'description',
        'url',
        'language',
        'stars',
        'forks',
        'score',
        'tags',
        'monetization',
        'difficulty',
        'is_featured',
        'is_vip',
        'category_id',
        'svip_subscription_id',
        'collected_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_featured' => 'boolean',
        'is_vip' => 'boolean',
        'score' => 'decimal:2',
        'collected_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /** VIP 项目需 VIP/SVIP/管理员可读详情（与文章 VIP 门槛一致）。 */
    public function userCanReadFull(?User $user): bool
    {
        if (! $this->is_vip) {
            return true;
        }

        if ($user === null || $user->is_banned) {
            return false;
        }

        return $user->canAccessVipExclusiveContent();
    }
}
