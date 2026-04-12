<?php

namespace App\Support;

use App\Models\EmailSubscription;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;

/**
 * 订阅摘要邮件：按用户 topic_schedule 的整点时刻发送；未填则用站点默认时间。
 */
final class SubscriptionDigestSchedule
{
    public static function defaultDailySlot(): string
    {
        return self::normalizeTime(SiteSetting::getValue('mail_sub_default_daily_time', '09:00'));
    }

    public static function defaultWeeklySlot(): string
    {
        return self::normalizeTime(SiteSetting::getValue('mail_sub_default_weekly_time', '10:00'));
    }

    /** ISO 星期几 1=周一 … 7=周日 */
    public static function weeklySendDayIso(): int
    {
        return max(1, min(7, (int) SiteSetting::getValue('mail_sub_weekly_send_weekday', '1')));
    }

    public static function currentClockSlot(): string
    {
        return now()->format('H:i');
    }

    /**
     * @param  array<string, mixed>|null  $topicSchedule
     */
    public static function effectiveDailySlot(?array $topicSchedule): string
    {
        $t = trim((string) data_get($topicSchedule, EmailSubscription::TOPIC_DAILY, ''));

        if ($t !== '' && preg_match('/^\d{2}:\d{2}$/', $t)) {
            return $t;
        }

        return self::defaultDailySlot();
    }

    /**
     * @param  array<string, mixed>|null  $topicSchedule
     */
    public static function effectiveWeeklySlot(?array $topicSchedule): string
    {
        $t = trim((string) data_get($topicSchedule, EmailSubscription::TOPIC_WEEKLY, ''));

        if ($t !== '' && preg_match('/^\d{2}:\d{2}$/', $t)) {
            return $t;
        }

        return self::defaultWeeklySlot();
    }

    public static function isWeeklySendDayToday(): bool
    {
        return (int) now()->isoWeekday() === self::weeklySendDayIso();
    }

    public static function hasDailyRecipientsNow(): bool
    {
        if (! Schema::hasTable('email_subscriptions')) {
            return false;
        }
        $slot = self::currentClockSlot();

        foreach (EmailSubscription::query()
            ->where('is_unsubscribed', false)
            ->whereJsonContains('subscribed_to', EmailSubscription::TOPIC_DAILY)
            ->cursor() as $sub) {
            if (self::effectiveDailySlot($sub->topic_schedule) !== $slot) {
                continue;
            }
            $email = trim((string) $sub->email);
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return true;
            }
        }

        return false;
    }

    public static function hasWeeklyRecipientsNow(): bool
    {
        if (! Schema::hasTable('email_subscriptions') || ! self::isWeeklySendDayToday()) {
            return false;
        }
        $slot = self::currentClockSlot();

        foreach (EmailSubscription::query()
            ->where('is_unsubscribed', false)
            ->whereJsonContains('subscribed_to', EmailSubscription::TOPIC_WEEKLY)
            ->cursor() as $sub) {
            if (self::effectiveWeeklySlot($sub->topic_schedule) !== $slot) {
                continue;
            }
            $email = trim((string) $sub->email);
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return true;
            }
        }

        return false;
    }

    private static function normalizeTime(string $raw): string
    {
        $raw = trim($raw);
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $raw, $m)) {
            return str_pad((string) (int) $m[1], 2, '0', STR_PAD_LEFT).':'.$m[2];
        }

        return '09:00';
    }
}
