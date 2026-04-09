<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SvipCustomSubscription extends Model
{
    protected $table = 'svip_custom_subscriptions';

    protected $fillable = [
        'user_id',
        'plan_name',
        'description',
        'delivery_frequency',
        'preferred_send_time',
        'delivery_channel',
        'amount',
        'duration_days',
        'services',
        'status',
        'started_at',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'services' => 'array',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
