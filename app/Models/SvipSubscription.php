<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SvipSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'keywords',
        'exclude_keywords',
        'sources',
        'frequency',
        'push_methods',
        'is_active',
        'last_fetch_at',
        'last_fetch_count',
    ];
    
    protected $casts = [
        'keywords' => 'array',
        'exclude_keywords' => 'array',
        'sources' => 'array',
        'push_methods' => 'array',
        'is_active' => 'boolean',
        'last_fetch_at' => 'datetime',
        'last_fetch_count' => 'integer',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
