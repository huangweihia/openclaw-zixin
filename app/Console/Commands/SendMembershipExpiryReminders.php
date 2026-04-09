<?php

namespace App\Console\Commands;

use App\Models\SiteSetting;
use App\Models\User;
use App\Services\WeCom\WeComMessageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendMembershipExpiryReminders extends Command
{
    protected $signature = 'membership:send-expiry-reminders';

    protected $description = '向 VIP/SVIP 用户发送会员即将到期提醒（邮件 + 已绑定企微 userid 时推送应用消息）';

    public function handle(WeComMessageService $wecom): int
    {
        if (SiteSetting::getValue('mail_batch_enabled', '1') === '1' && ! \App\Console\Kernel::mailBatchHourAllowed()) {
            $this->info('当前小时不在邮件批处理时间窗，跳过。');

            return self::SUCCESS;
        }

        $targets = [
            now()->addDays(1)->toDateString(),
            now()->addDays(3)->toDateString(),
            now()->addDays(7)->toDateString(),
        ];

        $site = SiteSetting::getValue('site_name', 'OpenClaw 智信');

        User::query()
            ->whereIn('role', ['vip', 'svip'])
            ->whereNotNull('subscription_ends_at')
            ->where('is_banned', false)
            ->orderBy('id')
            ->chunkById(100, function ($users) use ($targets, $site, $wecom) {
                foreach ($users as $user) {
                    $end = $user->subscription_ends_at;
                    if ($end === null || $end->isPast()) {
                        continue;
                    }
                    if (! in_array($end->toDateString(), $targets, true)) {
                        continue;
                    }

                    $plain = "【{$site}】您的会员将在 {$end->toDateString()} 到期，请及时续费以免权益中断。";

                    try {
                        Mail::raw($plain, function ($message) use ($user, $site) {
                            $message->to($user->email)->subject($site.' · 会员即将到期提醒');
                        });
                    } catch (\Throwable $e) {
                        $this->warn('邮件发送失败 user#'.$user->id.'：'.$e->getMessage());
                    }

                    $wid = trim((string) ($user->enterprise_wechat_id ?? ''));
                    if ($wid !== '') {
                        $r = $wecom->sendTextToUser($wid, $plain);
                        if (! ($r['ok'] ?? false)) {
                            $this->warn('企微推送失败 user#'.$user->id.'：'.($r['message'] ?? ''));
                        }
                    }
                }
            });

        $this->info('到期提醒任务已执行。');

        return self::SUCCESS;
    }
}
