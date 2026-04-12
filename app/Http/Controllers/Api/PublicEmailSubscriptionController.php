<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailSubscription;
use App\Services\SubscriptionEmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * 前台订阅 / 退订（供落地页、邮件退订链等调用）。
 */
class PublicEmailSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if (! Schema::hasTable('email_subscriptions')) {
            return response()->json(['message' => '服务暂不可用'], 503);
        }

        $user = $request->user();
        if (! $user || ! $user->canAccessVipExclusiveContent()) {
            return response()->json(['message' => '仅 VIP / SVIP / 管理员可订阅'], 403);
        }

        $data = $request->validate([
            'subscribed_to' => ['nullable', 'array', 'min:1'],
            'subscribed_to.*' => ['string', Rule::in(EmailSubscription::TOPICS)],
            'topic_schedule' => ['nullable', 'array'],
        ]);

        $topics = $data['subscribed_to'] ?? [EmailSubscription::TOPIC_NOTIFICATION];

        $email = (string) $user->email;
        $sub = EmailSubscription::query()->firstOrNew(['email' => $email]);
        $wasNew = ! $sub->exists;
        $sub->user_id = $user->id;
        $sub->subscribed_to = $topics;
        $topicSchedule = [];
        foreach (($data['topic_schedule'] ?? []) as $topic => $time) {
            if (! in_array((string) $topic, EmailSubscription::TOPICS, true)) {
                continue;
            }
            $timeStr = trim((string) $time);
            if (preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $timeStr)) {
                $topicSchedule[(string) $topic] = $timeStr;
            }
        }
        $sub->topic_schedule = $topicSchedule ?: null;
        $sub->is_unsubscribed = false;
        $sub->unsubscribed_at = null;
        if (empty($sub->unsubscribe_token)) {
            $sub->unsubscribe_token = \Illuminate\Support\Str::random(48);
        }
        $sub->save();

        try {
            app(SubscriptionEmailService::class)->sendSubscriptionSaved($sub, $user, $wasNew);
        } catch (\Throwable $e) {
            Log::warning('subscription confirmation email skipped', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'ok' => true,
            'email' => $sub->email,
            'subscribed_to' => $sub->subscribed_to,
            'topic_schedule' => $sub->topic_schedule,
        ], $wasNew ? 201 : 200);
    }

    public function unsubscribe(string $token): JsonResponse
    {
        if (! Schema::hasTable('email_subscriptions')) {
            return response()->json(['message' => '服务暂不可用'], 503);
        }

        $sub = EmailSubscription::query()->where('unsubscribe_token', $token)->first();
        if (! $sub) {
            return response()->json(['message' => '无效或已失效的退订链接'], 404);
        }

        $sub->markUnsubscribed();

        return response()->json(['ok' => true, 'message' => '已退订']);
    }
}
