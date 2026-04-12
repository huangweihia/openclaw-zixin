<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PrivateTrafficSop extends Model
{
    protected $table = 'private_traffic_sops';

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'content',
        'contact_note',
        'vip_gate_engagement',
        'platform',
        'type',
        'checklist',
        'templates',
        'metrics',
        'tools',
        'visibility',
        'view_count',
        'like_count',
        'favorite_count',
    ];

    protected $casts = [
        'vip_gate_engagement' => 'boolean',
        'checklist' => 'array',
        'templates' => 'array',
        'metrics' => 'array',
        'tools' => 'array',
    ];

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function userCanReadFull(?User $user): bool
    {
        if (($this->visibility ?? 'public') !== 'vip') {
            return true;
        }

        if ($user === null || $user->is_banned) {
            return false;
        }

        return $user->canAccessVipExclusiveContent();
    }
}
