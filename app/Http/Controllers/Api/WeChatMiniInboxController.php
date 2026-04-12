<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InboxNotification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

/**
 * 小程序站内消息（notifications 表，与官网消息中心同源）
 */
class WeChatMiniInboxController extends Controller
{
    public function unreadCount(Request $request): JsonResponse
    {
        if (! Schema::hasTable('notifications')) {
            return response()->json(['success' => true, 'count' => 0]);
        }

        /** @var User $user */
        $user = $request->user();
        $count = InboxNotification::query()
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['success' => true, 'count' => $count]);
    }

    public function index(Request $request): JsonResponse
    {
        if (! Schema::hasTable('notifications')) {
            return response()->json([
                'success' => true,
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 20,
                    'total' => 0,
                ],
            ]);
        }

        /** @var User $user */
        $user = $request->user();
        $perPage = min(max((int) $request->input('per_page', 20), 1), 50);

        $query = InboxNotification::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at');

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($perPage);

        $data = collect($paginator->items())->map(function (InboxNotification $n) {
            return [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'content' => $n->content,
                'action_url' => $n->action_url,
                'is_read' => (bool) $n->is_read,
                'read_at' => $n->read_at?->toIso8601String(),
                'created_at' => $n->created_at?->toIso8601String(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function markRead(Request $request, int $id): JsonResponse
    {
        if (! Schema::hasTable('notifications')) {
            return response()->json(['message' => '服务暂不可用'], 503);
        }

        /** @var User $user */
        $user = $request->user();
        $row = InboxNotification::query()
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();
        $row->markAsRead();

        return response()->json(['success' => true]);
    }

    public function readAll(Request $request): JsonResponse
    {
        if (! Schema::hasTable('notifications')) {
            return response()->json(['message' => '服务暂不可用'], 503);
        }

        /** @var User $user */
        $user = $request->user();
        InboxNotification::query()
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }
}
