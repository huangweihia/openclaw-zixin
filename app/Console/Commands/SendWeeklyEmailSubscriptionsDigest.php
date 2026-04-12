<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\EmailSetting;
use App\Models\EmailSubscription;
use App\Models\SiteSetting;
use App\Services\SubscriptionEmailService;
use App\Support\EmailLogWriter;
use App\Support\SubscriptionDigestPlaceholders;
use App\Support\SubscriptionDigestSchedule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SendWeeklyEmailSubscriptionsDigest extends Command
{
    protected $signature = 'subscriptions:send-weekly-digest';

    protected $description = '向勾选「每周精选」的订阅用户发送文章摘要（模板与占位符与每日一致，条数更多）';

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

        if (! SubscriptionDigestSchedule::hasWeeklyRecipientsNow()) {
            return self::SUCCESS;
        }

        $site = SiteSetting::getValue('site_name', 'OpenClaw 智信');
        $articles = Article::query()
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->limit(15)
            ->get();

        if ($articles->isEmpty()) {
            $this->info('无已发布文章，跳过发送。');

            return self::SUCCESS;
        }

        $lines = $articles->map(function (Article $a) {
            $u = route('articles.show', $a);

            return '<li><a href="'.e($u).'">'.e($a->title).'</a>'.($a->summary ? ' — '.e(Str::limit($a->summary, 80)) : '').'</li>';
        })->implode('');

        $articleListHtml = '<ul>'.$lines.'</ul>';
        $dateStr = now()->format('Y-m-d');
        $placeholders = SubscriptionDigestPlaceholders::build($site, $articleListHtml, $dateStr, $articles);
        $service = app(SubscriptionEmailService::class);
        $tplKey = $service->resolveTemplateKey(EmailSubscription::TOPIC_WEEKLY);
        $rendered = $service->render($tplKey, $placeholders);
        if ($rendered) {
            $html = $rendered['html'];
            $digestSubject = $rendered['subject'];
        } else {
            $html = '<p>'.e($site).' 每周精选：</p>'.$articleListHtml.'<p style="font-size:12px;color:#64748b">此为自动邮件，退订请登录站点邮箱订阅设置。</p>';
            $digestSubject = $site.' · 每周内容精选';
        }

        $sent = 0;
        $batchSize = max(20, min(1000, (int) (EmailSetting::query()->where('key', 'mail_sub_batch_size')->value('value') ?? 200)));
        $dailyCap = max(0, (int) (EmailSetting::query()->where('key', 'mail_sub_daily_cap')->value('value') ?? 0));
        $slot = SubscriptionDigestSchedule::currentClockSlot();
        $processed = 0;
        EmailSubscription::query()
            ->where('is_unsubscribed', false)
            ->whereJsonContains('subscribed_to', EmailSubscription::TOPIC_WEEKLY)
            ->orderBy('id')
            ->chunkById($batchSize, function ($chunk) use ($html, &$sent, &$processed, $dailyCap, $digestSubject, $slot) {
                foreach ($chunk as $sub) {
                    if ($dailyCap > 0 && $processed >= $dailyCap) {
                        return false;
                    }
                    if (SubscriptionDigestSchedule::effectiveWeeklySlot($sub->topic_schedule) !== $slot) {
                        continue;
                    }
                    $email = trim((string) $sub->email);
                    if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        continue;
                    }
                    $logMeta = [
                        'topic' => 'weekly',
                        'scheduled_slot' => SubscriptionDigestSchedule::effectiveWeeklySlot($sub->topic_schedule),
                        'email_subscription_id' => $sub->id,
                        'weekly_weekday_iso' => SubscriptionDigestSchedule::weeklySendDayIso(),
                    ];
                    try {
                        $subject = $digestSubject;
                        Mail::html($html, function ($message) use ($email, $subject) {
                            $message->to($email)->subject($subject);
                        });
                        EmailLogWriter::sent($sub->user_id ? (int) $sub->user_id : null, $email, $subject, 'weekly_digest', $logMeta);
                        $sent++;
                        $processed++;
                    } catch (\Throwable $e) {
                        EmailLogWriter::failed($sub->user_id ? (int) $sub->user_id : null, $email, $digestSubject, $e->getMessage(), 'weekly_digest', $logMeta);
                        $this->warn('发送失败 '.$email.'：'.$e->getMessage());
                        $processed++;
                    }
                }
            });

        if ($sent > 0) {
            $this->info("本分钟已发送 {$sent} 封每周摘要（时刻 {$slot}）。");
        }

        return self::SUCCESS;
    }
}
