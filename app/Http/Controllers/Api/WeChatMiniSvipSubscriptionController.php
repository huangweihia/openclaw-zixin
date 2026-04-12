<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SvipSubscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 当前登录用户的 SVIP 咨询订阅（只读，供小程序）
 */
class WeChatMiniSvipSubscriptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $rows = SvipSubscription::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (SvipSubscription $s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'keywords' => $s->keywords ?? [],
                    'exclude_keywords' => $s->exclude_keywords ?? [],
                    'sources' => $s->sources ?? [],
                    'frequency' => $s->frequency,
                    'push_methods' => $s->push_methods ?? [],
                    'is_active' => (bool) $s->is_active,
                    'last_fetch_at' => $s->last_fetch_at?->toIso8601String(),
                    'last_fetch_count' => $s->last_fetch_count,
                    'created_at' => $s->created_at?->toIso8601String(),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }
}
