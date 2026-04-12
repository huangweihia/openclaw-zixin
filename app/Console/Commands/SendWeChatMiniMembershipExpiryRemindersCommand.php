<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\WeChatMini\SubscribeMessageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SendWeChatMiniMembershipExpiryRemindersCommand extends Command
{
    protected $signature = 'wechat-mini:send-membership-expiry-reminders {--dry-run : 只统计不发送}';

    protected $description = '向临近会员到期的微信小程序用户发送订阅消息（需已配置模板与用户曾授权）';

    public function handle(SubscribeMessageService $subscribe): int
    {
        $template = app(SubscribeMessageService::class)->membershipExpiryTemplateId();
        if ($template === '') {
            $this->warn('未配置 WECHAT_MINI_SUBSCRIBE_TEMPLATE_IDS（或 WECHAT_MINI_SUBSCRIBE_MEMBERSHIP_EXPIRY_TEMPLATE_ID），跳过。');

            return self::SUCCESS;
        }

        $days = max(1, min(14, (int) config('wechat.mini_subscribe_expiry_days_before', 3)));
        $dry = (bool) $this->option('dry-run');

        $query = User::query()
            ->where('is_banned', false)
            ->whereNotNull('subscription_ends_at')
            ->where('subscription_ends_at', '>', now())
            ->where('subscription_ends_at', '<=', now()->addDays($days))
            ->whereIn('role', ['vip', 'svip', 'admin'])
            ->where(function ($q) {
                $q->whereNotNull('wechat_mini_openid')
                    ->where('wechat_mini_openid', '!=', '')
                    ->orWhere(function ($q2) {
                        $q2->whereNotNull('wechat_openid')->where('wechat_openid', '!=', '');
                    });
            });

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        $query->orderBy('id')->chunkById(200, function ($users) use ($subscribe, $dry, &$sent, &$skipped, &$failed) {
            foreach ($users as $user) {
                $cacheKey = 'wxmini:sub_expiry:'.$user->id.':'.($user->subscription_ends_at?->timestamp ?? 0);
                if (Cache::has($cacheKey)) {
                    $skipped++;

                    continue;
                }

                if ($dry) {
                    $this->line("[dry-run] user {$user->id} expires {$user->subscription_ends_at}");
                    $skipped++;

                    continue;
                }

                $r = $subscribe->sendMembershipExpiryReminder($user);
                if (! empty($r['ok'])) {
                    Cache::put($cacheKey, 1, now()->addDays(45));
                    $sent++;
                } else {
                    // 43101 用户未授权/次数用完等，不缓存以便下次活动后再试
                    if (($r['errcode'] ?? 0) === 43101) {
                        $skipped++;
                    } else {
                        $failed++;
                    }
                }
            }
        });

        $this->info("完成：发送成功 {$sent}，跳过 {$skipped}，失败 {$failed}".($dry ? '（dry-run）' : ''));

        return self::SUCCESS;
    }
}
