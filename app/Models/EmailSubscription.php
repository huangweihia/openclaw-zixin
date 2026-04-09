<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EmailSubscription extends Model
{
    public const TOPIC_DAILY = 'daily';

    public const TOPIC_WEEKLY = 'weekly';

    public const TOPIC_NOTIFICATION = 'notification';

    public const TOPIC_PROMOTION = 'promotion';

    public const TOPICS = [
        self::TOPIC_DAILY,
        self::TOPIC_WEEKLY,
        self::TOPIC_NOTIFICATION,
        self::TOPIC_PROMOTION,
    ];

    protected $fillable = [
        'user_id',
        'email',
        'subscribed_to',
        'is_unsubscribed',
        'unsubscribed_at',
        'unsubscribe_token',
    ];

    protected $casts = [
        'subscribed_to' => 'array',
        'is_unsubscribed' => 'boolean',
        'unsubscribed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (EmailSubscription $model) {
            if (empty($model->unsubscribe_token)) {
                $model->unsubscribe_token = Str::random(48);
            }
            if ($model->subscribed_to === null || $model->subscribed_to === []) {
                $model->subscribed_to = [self::TOPIC_NOTIFICATION];
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markUnsubscribed(): void
    {
        $this->is_unsubscribed = true;
        $this->unsubscribed_at = now();
        $this->save();
    }

    public function markSubscribed(array $topics): void
    {
        $this->subscribed_to = $topics;
        $this->is_unsubscribed = false;
        $this->unsubscribed_at = null;
        $this->save();
    }

    public function wantsTopic(string $topic): bool
    {
        if ($this->is_unsubscribed) {
            return false;
        }

        return in_array($topic, $this->subscribed_to ?? [], true);
    }
}
