<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushNotification;
use App\Services\PushNotificationInboxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushNotificationController extends Controller
{
    public function __construct(
        private PushNotificationInboxService $pushInbox,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userId = $request->query('user_id');

        $q = PushNotification::query()->with('user:id,name,email')->orderByDesc('id');
        if ($userId !== null && $userId !== '') {
            $q->where('user_id', (int) $userId);
        }

        return response()->json($q->paginate(30)->withQueryString());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'action_url' => ['nullable', 'string', 'max:255'],
            'data' => ['nullable', 'array'],
            'is_sent' => ['sometimes', 'boolean'],
            'is_read' => ['sometimes', 'boolean'],
        ]);
        $row = PushNotification::query()->create($data);
        if (! empty($data['is_sent'])) {
            $row->forceFill(['sent_at' => now()])->save();
        }
        $this->pushInbox->syncFromPush($row->fresh());

        return response()->json([
            'message' => '已创建',
            'notification' => $row->fresh()->load('user:id,name,email'),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = PushNotification::query()->findOrFail($id);
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
            'action_url' => ['nullable', 'string', 'max:255'],
            'data' => ['nullable', 'array'],
            'is_sent' => ['sometimes', 'boolean'],
            'is_read' => ['sometimes', 'boolean'],
            'sent_at' => ['nullable', 'date'],
            'read_at' => ['nullable', 'date'],
        ]);
        $row->fill($data);
        if (array_key_exists('is_sent', $data) && $data['is_sent'] && $row->sent_at === null) {
            $row->sent_at = now();
        }
        if (array_key_exists('is_read', $data) && $data['is_read'] && $row->read_at === null) {
            $row->read_at = now();
        }
        $row->save();
        $this->pushInbox->syncFromPush($row->fresh());

        return response()->json([
            'message' => '已更新',
            'notification' => $row->fresh()->load('user:id,name,email'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->pushInbox->deleteForPush($id);
        PushNotification::query()->whereKey($id)->delete();

        return response()->json(['message' => '已删除']);
    }
}
