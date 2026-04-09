<?php

namespace App\Services;

use App\Models\InboxNotification;
use App\Models\PushNotification;
use Illuminate\Support\Facades\Schema;

/**
 * 后台「站内推送」与前台通知中心（notifications 表）同步。
 */
class PushNotificationInboxService
{
    public function syncFromPush(PushNotification $push): void
    {
        if (! Schema::hasColumn('notifications', 'push_notification_id')) {
            return;
        }

        $inbox = InboxNotification::query()
            ->where('push_notification_id', $push->id)
            ->where('user_id', $push->user_id)
            ->first();

        $payload = [
            'user_id' => $push->user_id,
            'type' => 'push',
            'title' => $push->title,
            'content' => $push->content,
            'action_url' => $push->action_url,
            'push_notification_id' => $push->id,
        ];

        if ($inbox) {
            $inbox->forceFill($payload)->save();
        } else {
            InboxNotification::query()->create($payload);
        }
    }

    public function deleteForPush(int $pushId): void
    {
        if (! Schema::hasColumn('notifications', 'push_notification_id')) {
            return;
        }
        InboxNotification::query()->where('push_notification_id', $pushId)->delete();
    }
}
