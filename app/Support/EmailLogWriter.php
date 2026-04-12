<?php

namespace App\Support;

use App\Models\EmailLog;

class EmailLogWriter
{
    /**
     * @param  array<string, mixed>|null  $meta  如 email_subscription_id、scheduled_slot、topic
     */
    public static function sent(?int $userId, string $to, string $subject, ?string $templateKey = null, ?array $meta = null): void
    {
        self::write($userId, $to, $subject, 'sent', null, $templateKey, $meta);
    }

    /**
     * @param  array<string, mixed>|null  $meta
     */
    public static function failed(?int $userId, string $to, string $subject, string $errorMessage, ?string $templateKey = null, ?array $meta = null): void
    {
        self::write($userId, $to, $subject, 'failed', $errorMessage, $templateKey, $meta);
    }

    /**
     * @param  array<string, mixed>|null  $meta
     */
    private static function write(?int $userId, string $to, string $subject, string $status, ?string $errorMessage, ?string $templateKey, ?array $meta): void
    {
        try {
            EmailLog::query()->create([
                'user_id' => $userId,
                'template_key' => $templateKey,
                'to' => $to,
                'subject' => $subject,
                'status' => $status,
                'error_message' => $errorMessage,
                'meta' => $meta,
                'sent_at' => $status === 'sent' ? now() : null,
            ]);
        } catch (\Throwable $e) {
            // 记录失败不应影响主流程
        }
    }
}
