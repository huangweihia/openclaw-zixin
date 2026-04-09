<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboxNotification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'push_notification_id',
        'type',
        'title',
        'content',
        'action_url',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        if ($this->is_read) {
            return;
        }
        $this->forceFill([
            'is_read' => true,
            'read_at' => now(),
        ])->save();
    }
}
