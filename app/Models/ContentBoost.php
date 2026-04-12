<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentBoost extends Model
{
    protected $fillable = [
        'actor_user_id',
        'user_post_id',
        'weight',
        'points_spent',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function userPost(): BelongsTo
    {
        return $this->belongsTo(UserPost::class, 'user_post_id');
    }

    public static function sumActiveWeightForPost(int $userPostId): int
    {
        return (int) static::query()
            ->where('user_post_id', $userPostId)
            ->where('ends_at', '>', now())
            ->sum('weight');
    }
}
