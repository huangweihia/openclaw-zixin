<?php

namespace App\Services;

use App\Models\PushNotification;
use Illuminate\Support\Facades\Schema;

/**
 * 单用户推送 + 同步到站内通知中心（notifications）。
 */
final class PushInboxDispatcher
{
    public static function send(int $userId, string $type, string $title, string $content, ?string $actionUrl = null): void
    {
        if (! Schema::hasTable('push_notifications')) {
            return;
        }

        $push = PushNotification::query()->create([
            'user_id' => $userId,
            'title' => $title,
            'content' => $content === '' ? ' ' : $content,
            'action_url' => $actionUrl,
            'data' => ['source_type' => $type],
            'is_sent' => true,
            'is_read' => false,
            'sent_at' => now(),
        ]);

        if (Schema::hasTable('notifications')) {
            app(PushNotificationInboxService::class)->syncFromPush($push->fresh());
        }

        if (Schema::hasTable('notifications')) {
            $inbox = \App\Models\InboxNotification::query()
                ->where('push_notification_id', $push->id)
                ->where('user_id', $userId)
                ->first();
            if ($inbox && $type !== 'push') {
                $inbox->forceFill(['type' => $type])->save();
            }
        }
    }
}
