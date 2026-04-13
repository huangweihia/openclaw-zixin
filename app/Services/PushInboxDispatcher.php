<?php

namespace App\Services;

use App\Models\PushNotification;
use App\Models\User;
use App\Support\EmailLogWriter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;

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

        self::sendMirrorEmail($userId, $title, $content, $actionUrl, $type);
    }

    private static function sendMirrorEmail(int $userId, string $title, string $content, ?string $actionUrl, string $type): void
    {
        if (! filter_var(env('PUSH_MAIL_ENABLED', true), FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $user = User::query()->whereKey($userId)->first();
        if (! $user || ! is_string($user->email) || trim($user->email) === '') {
            return;
        }

        $subject = '【站内提醒】'.$title;
        $body = trim($content);
        if ($actionUrl) {
            $body .= ($body !== '' ? "\n\n" : '').'查看详情：'.$actionUrl;
        }
        if ($body === '') {
            $body = '你收到一条新的站内提醒。';
        }

        try {
            Mail::raw($body, function ($message) use ($user, $subject) {
                $message->to((string) $user->email)->subject($subject);
            });
            EmailLogWriter::sent((int) $user->id, (string) $user->email, $subject, 'push_mail', ['source_type' => $type]);
        } catch (\Throwable $e) {
            EmailLogWriter::failed((int) $user->id, (string) $user->email, $subject, $e->getMessage(), 'push_mail', ['source_type' => $type]);
        }
    }
}
