<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublishAudit extends Model
{
    protected $fillable = [
        'publish_id',
        'user_id',
        'auditor_id',
        'status',
        'reject_reason',
        'suggest',
        'priority',
        'audited_at',
    ];

    protected $casts = [
        'audited_at' => 'datetime',
    ];

    public function userPost(): BelongsTo
    {
        return $this->belongsTo(UserPost::class, 'publish_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }
}
