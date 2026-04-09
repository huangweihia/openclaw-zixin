<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSkin extends Model
{
    protected $fillable = [
        'user_id',
        'skin_id',
        'activated_at',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skinConfig(): BelongsTo
    {
        return $this->belongsTo(SkinConfig::class, 'skin_id');
    }
}
