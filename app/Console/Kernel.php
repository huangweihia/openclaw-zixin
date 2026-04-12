<?php

namespace App\Console;

use App\Models\SiteSetting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('membership:send-expiry-reminders')->dailyAt('09:00');
        $schedule->command('subscriptions:send-daily-digest')->dailyAt('08:30');
        $schedule->command('wechat-mini:send-membership-expiry-reminders')->dailyAt('10:00');
    }

    /** @internal 供调度闭包示例 */
    public static function mailBatchHourAllowed(): bool
    {
        if (SiteSetting::getValue('mail_batch_enabled', '1') !== '1') {
            return true;
        }
        $start = max(0, min(23, (int) SiteSetting::getValue('mail_batch_start_hour', '9')));
        $end = max(0, min(23, (int) SiteSetting::getValue('mail_batch_end_hour', '22')));
        $h = (int) now()->format('G');
        if ($start <= $end) {
            return $h >= $start && $h <= $end;
        }

        return $h >= $start || $h <= $end;
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
