<?php

namespace App\Support;

use App\Models\EmailLog;

class EmailLogWriter
{
    public static function sent(?int $userId, string $to, string $subject, ?string $templateKey = null): void
    {
        self::write($userId, $to, $subject, 'sent', null, $templateKey);
    }

    public static function failed(?int $userId, string $to, string $subject, string $errorMessage, ?string $templateKey = null): void
    {
        self::write($userId, $to, $subject, 'failed', $errorMessage, $templateKey);
    }

    private static function write(?int $userId, string $to, string $subject, string $status, ?string $errorMessage, ?string $templateKey): void
    {
        try {
            EmailLog::query()->create([
                'user_id' => $userId,
                'template_key' => $templateKey,
                'to' => $to,
                'subject' => $subject,
                'status' => $status,
                'error_message' => $errorMessage,
                'sent_at' => $status === 'sent' ? now() : null,
            ]);
        } catch (\Throwable $e) {
            // 记录失败不应影响主流程
        }
    }
}
