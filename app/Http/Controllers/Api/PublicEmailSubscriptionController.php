<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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
        if (! $user || ! in_array((string) $user->role, ['vip', 'svip', 'admin'], true)) {
            return response()->json(['message' => '仅 VIP / SVIP / 管理员可订阅'], 403);
        }

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'subscribed_to' => ['nullable', 'array', 'min:1'],
            'subscribed_to.*' => ['string', Rule::in(EmailSubscription::TOPICS)],
        ]);

        $topics = $data['subscribed_to'] ?? [EmailSubscription::TOPIC_NOTIFICATION];

        $sub = EmailSubscription::query()->firstOrNew(['email' => $data['email']]);
        $wasNew = ! $sub->exists;
        if ($wasNew) {
            $sub->user_id = $user->id;
        }
        $sub->subscribed_to = $topics;
        $sub->is_unsubscribed = false;
        $sub->unsubscribed_at = null;
        if (empty($sub->unsubscribe_token)) {
            $sub->unsubscribe_token = \Illuminate\Support\Str::random(48);
        }
        $sub->save();

        return response()->json([
            'ok' => true,
            'email' => $sub->email,
            'subscribed_to' => $sub->subscribed_to,
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
