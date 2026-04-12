<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * 小程序：当前用户邮件订阅主题（GET，与 PublicEmailSubscriptionController::store 配套）
 */
class WeChatMiniEmailSubscriptionController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        if (! Schema::hasTable('email_subscriptions')) {
            return response()->json(['message' => '服务暂不可用'], 503);
        }

        $user = $request->user();
        if (! $user || ! $user->canAccessVipExclusiveContent()) {
            return response()->json(['message' => '仅 VIP / SVIP / 管理员可使用邮件订阅'], 403);
        }

        $email = $request->string('email')->trim()->toString();
        if ($email === '') {
            $email = (string) ($user->email ?? '');
        }
        if ($email === '' || str_ends_with($email, '@users.openclaw.local')) {
            return response()->json([
                'success' => true,
                'data' => null,
                'hint' => '请先绑定真实邮箱后再管理邮件订阅',
            ]);
        }

        $sub = EmailSubscription::query()
            ->where('user_id', $user->id)
            ->where('email', $email)
            ->first();

        if (! $sub) {
            return response()->json([
                'success' => true,
                'data' => [
                    'email' => $email,
                    'subscribed_to' => [EmailSubscription::TOPIC_NOTIFICATION],
                    'topic_schedule' => null,
                    'is_unsubscribed' => false,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'email' => $sub->email,
                'subscribed_to' => $sub->subscribed_to ?? [EmailSubscription::TOPIC_NOTIFICATION],
                'topic_schedule' => $sub->topic_schedule,
                'is_unsubscribed' => (bool) $sub->is_unsubscribed,
            ],
        ]);
    }
}
