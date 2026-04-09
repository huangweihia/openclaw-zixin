<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SideHustleCase extends Model
{
    protected $table = 'side_hustle_cases';

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'content',
        'category',
        'type',
        'startup_cost',
        'time_investment',
        'resource_type',      // 新增：资源类型
        'resource_url',       // 新增：原始资源地址
        'estimated_income',
        'actual_income',
        'income_screenshots',
        'steps',
        'tools',
        'pitfalls',
        'willing_to_consult',
        'contact_info',
        'visibility',
        'status',
        'audit_note',
        'audited_by',
        'audited_at',
        'view_count',
        'like_count',
        'comment_count',
        'favorite_count',
        'user_id',
    ];

    protected $casts = [
        'income_screenshots' => 'array',
        'tools' => 'array',
        'pitfalls' => 'array',
        'willing_to_consult' => 'boolean',
        'estimated_income' => 'decimal:2',
        'actual_income' => 'decimal:2',
        'audited_at' => 'datetime',
    ];

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'audited_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
