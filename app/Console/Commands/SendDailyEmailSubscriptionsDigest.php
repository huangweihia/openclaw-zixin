<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\EmailSubscription;
use App\Models\SiteSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SendDailyEmailSubscriptionsDigest extends Command
{
    protected $signature = 'subscriptions:send-daily-digest';

    protected $description = '向勾选「每日」主题的邮箱订阅用户发送最新内容摘要';

    public function handle(): int
    {
        if (SiteSetting::getValue('mail_batch_enabled', '1') === '1' && ! \App\Console\Kernel::mailBatchHourAllowed()) {
            $this->info('当前小时不在邮件批处理时间窗，跳过。');

            return self::SUCCESS;
        }

        if (! Schema::hasTable('email_subscriptions') || ! Schema::hasTable('articles')) {
            $this->warn('缺少数据表，跳过。');

            return self::SUCCESS;
        }

        $site = SiteSetting::getValue('site_name', 'OpenClaw 智信');
        $articles = Article::query()
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->limit(8)
            ->get(['title', 'slug', 'summary']);

        if ($articles->isEmpty()) {
            $this->info('无已发布文章，跳过发送。');

            return self::SUCCESS;
        }

        $lines = $articles->map(function (Article $a) {
            $u = route('articles.show', $a);

            return '<li><a href="'.e($u).'">'.e($a->title).'</a>'.($a->summary ? ' — '.e(Str::limit($a->summary, 80)) : '').'</li>';
        })->implode('');

        $html = '<p>'.e($site).' 每日精选：</p><ul>'.$lines.'</ul><p style="font-size:12px;color:#64748b">此为自动邮件，退订请登录站点邮箱订阅设置。</p>';

        $sent = 0;
        EmailSubscription::query()
            ->where('is_unsubscribed', false)
            ->whereJsonContains('subscribed_to', EmailSubscription::TOPIC_DAILY)
            ->orderBy('id')
            ->chunkById(200, function ($chunk) use ($html, $site, &$sent) {
                foreach ($chunk as $sub) {
                    $email = trim((string) $sub->email);
                    if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        continue;
                    }
                    try {
                        Mail::html($html, function ($message) use ($email, $site) {
                            $message->to($email)->subject($site.' · 每日内容精选');
                        });
                        $sent++;
                    } catch (\Throwable $e) {
                        $this->warn('发送失败 '.$email.'：'.$e->getMessage());
                    }
                }
            });

        $this->info("已尝试发送 {$sent} 封每日摘要。");

        return self::SUCCESS;
    }
}
