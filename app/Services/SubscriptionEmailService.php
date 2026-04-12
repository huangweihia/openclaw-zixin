<?php

namespace App\Services;

use App\Models\EmailSubscription;
use App\Models\EmailSubscriptionTopicTemplate;
use App\Models\EmailTemplate;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\EmailLogWriter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class SubscriptionEmailService
{
    /** @var array<string, string> */
    private const DEFAULT_TEMPLATE_BY_TOPIC = [
        'subscription_saved' => 'sub_saved_confirmation',
        'daily' => 'sub_daily_digest',
        'weekly' => 'sub_weekly_digest',
        'notification' => 'sub_notification_digest',
        'promotion' => 'sub_promotion_digest',
    ];

    public function resolveTemplateKey(string $topicKey): string
    {
        if (Schema::hasTable('email_subscription_topic_templates')) {
            $key = EmailSubscriptionTopicTemplate::query()->where('topic_key', $topicKey)->value('template_key');
            if (is_string($key) && $key !== '') {
                return $key;
            }
        }

        return self::DEFAULT_TEMPLATE_BY_TOPIC[$topicKey] ?? 'sub_saved_confirmation';
    }

    /**
     * @param  array<string, string>  $placeholders  键为占位符名（不含 {{}}）
     * @return array{subject: string, html: string, plain: string}|null
     */
    public function render(string $templateKey, array $placeholders): ?array
    {
        $tpl = EmailTemplate::query()
            ->where('key', $templateKey)
            ->where('is_active', true)
            ->first();
        if (! $tpl) {
            return null;
        }
        $vars = [];
        foreach ($placeholders as $k => $v) {
            $vars['{{'.$k.'}}'] = $v;
        }

        return [
            'subject' => strtr($tpl->subject, $vars),
            'html' => strtr($tpl->content, $vars),
            'plain' => $tpl->plain_text
                ? strtr($tpl->plain_text, $vars)
                : strip_tags(strtr($tpl->content, $vars)),
        ];
    }

    public function sendSubscriptionSaved(EmailSubscription $subscription, User $user, bool $wasNew): void
    {
        $site = SiteSetting::getValue('site_name', config('app.name', 'OpenClaw 智信'));
        $templateKey = $this->resolveTemplateKey('subscription_saved');
        $labels = [
            EmailSubscription::TOPIC_DAILY => '每日精选',
            EmailSubscription::TOPIC_WEEKLY => '每周精选',
            EmailSubscription::TOPIC_NOTIFICATION => '系统通知',
            EmailSubscription::TOPIC_PROMOTION => '活动推广',
        ];
        $topics = array_map(fn (string $t) => $labels[$t] ?? $t, $subscription->subscribed_to ?? []);
        $topicsText = implode('、', $topics) ?: '无';
        $scheduleBits = [];
        foreach ($subscription->topic_schedule ?? [] as $tk => $time) {
            if (isset($labels[$tk])) {
                $scheduleBits[] = $labels[$tk].' '.(string) $time;
            }
        }
        $scheduleText = $scheduleBits ? implode('；', $scheduleBits) : '默认发送时间';
        $manageUrl = route('dashboard');
        $unsubscribeUrl = url('/api/email-unsubscribe/'.$subscription->unsubscribe_token);
        $isNewLine = $wasNew ? '欢迎订阅我们的邮件' : '您的邮件订阅设置已更新';

        $rendered = $this->render($templateKey, [
            'user_name' => $user->name,
            'site_name' => $site,
            'email' => $subscription->email,
            'topics_list' => $topicsText,
            'topic_schedule_summary' => $scheduleText,
            'manage_url' => $manageUrl,
            'unsubscribe_url' => $unsubscribeUrl,
            'is_new_subscription' => $isNewLine,
        ]);

        if (! $rendered) {
            $this->sendFallbackSaved($subscription, $user, $site, $wasNew);

            return;
        }

        $this->mail($subscription, $user, $rendered, 'subscription_saved');
    }

    /**
     * @param  array{subject: string, html: string, plain: string}  $rendered
     * @param  array<string, mixed>|null  $logMeta  写入 email_logs.meta
     */
    public function mail(EmailSubscription $sub, ?User $user, array $rendered, string $logKey, ?array $logMeta = null): void
    {
        $uid = $user?->id ?? ($sub->user_id ? (int) $sub->user_id : null);
        $meta = array_merge([
            'email_subscription_id' => $sub->id,
            'event' => $logKey,
        ], $logMeta ?? []);
        try {
            Mail::html($rendered['html'], function ($message) use ($sub, $rendered) {
                $message->to($sub->email)->subject($rendered['subject']);
                if ($rendered['plain'] !== '') {
                    $message->text($rendered['plain']);
                }
            });
            EmailLogWriter::sent($uid, (string) $sub->email, $rendered['subject'], $logKey, $meta);
        } catch (\Throwable $e) {
            EmailLogWriter::failed($uid, (string) $sub->email, $rendered['subject'], $e->getMessage(), $logKey, $meta);
            Log::warning('subscription mail failed', [
                'log_key' => $logKey,
                'email' => $sub->email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendFallbackSaved(EmailSubscription $subscription, User $user, string $site, bool $wasNew): void
    {
        $line = $wasNew ? '欢迎订阅' : '订阅设置已更新';
        $html = '<p>您好 <strong>'.e($user->name).'</strong>，</p>'
            .'<p>'.e($line).'（'.e($site).'）。收件邮箱：'.e($subscription->email).'</p>'
            .'<p><a href="'.e(route('dashboard')).'">管理订阅</a></p>';
        $this->mail($subscription, $user, [
            'subject' => $site.' · '.$line,
            'html' => $html,
            'plain' => strip_tags($html),
        ], 'subscription_saved_fallback', [
            'email_subscription_id' => $subscription->id,
            'fallback' => true,
        ]);
    }
}
