<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemNotification;
use App\Services\SystemNotificationInboxDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SystemNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // 兜底：修复历史上“已发布但未入站内信”的遗留通知。
        app(SystemNotificationInboxDispatcher::class)->dispatchBacklog(50);

        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(10, min($perPage, 100));
        $q = trim((string) $request->query('q', ''));

        $rows = SystemNotification::query()
            ->with('creator:id,name')
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($sub) use ($q) {
                    $sub->where('title', 'like', '%'.$q.'%')
                        ->orWhere('content', 'like', '%'.$q.'%');
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($rows);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateBody($request);
        $row = new SystemNotification($data);
        $row->created_by = $request->user()->id;
        if (! empty($data['is_published'])) {
            $row->published_at = now();
        }
        $row->save();

        $fresh = $row->fresh()->load('creator:id,name');
        app(SystemNotificationInboxDispatcher::class)->dispatchIfNeeded($fresh);

        return response()->json([
            'message' => '已创建',
            'notification' => $fresh->refresh()->load('creator:id,name'),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = SystemNotification::query()->findOrFail($id);
        $data = $this->validateBody($request, false);
        $row->fill($data);
        if (array_key_exists('is_published', $data) && $data['is_published'] && $row->published_at === null) {
            $row->published_at = now();
        }
        $row->save();

        $fresh = $row->fresh()->load('creator:id,name');
        app(SystemNotificationInboxDispatcher::class)->dispatchIfNeeded($fresh);

        return response()->json([
            'message' => '已更新',
            'notification' => $fresh->refresh()->load('creator:id,name'),
        ]);
    }

    public function togglePublish(int $id): JsonResponse
    {
        $row = SystemNotification::query()->findOrFail($id);
        $row->is_published = ! $row->is_published;
        if ($row->is_published && $row->published_at === null) {
            $row->published_at = now();
        }
        $row->save();

        $fresh = $row->fresh()->load('creator:id,name');
        app(SystemNotificationInboxDispatcher::class)->dispatchIfNeeded($fresh);

        return response()->json([
            'message' => $row->is_published ? '已发布' : '已下架',
            'notification' => $fresh->refresh()->load('creator:id,name'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        SystemNotification::query()->whereKey($id)->delete();

        return response()->json(['message' => '已删除']);
    }

    private function validateBody(Request $request, bool $requireAll = true): array
    {
        $rules = [
            'title' => [$requireAll ? 'required' : 'sometimes', 'string', 'max:255'],
            'content' => [$requireAll ? 'required' : 'sometimes', 'string'],
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high'])],
            'type' => ['sometimes', Rule::in(['system', 'announcement', 'maintenance'])],
            'audience' => ['sometimes', Rule::in(['all', 'user', 'vip', 'svip', 'admin', 'member', 'non_member'])],
            'action_url' => ['nullable', 'string', 'max:255'],
            'is_published' => ['sometimes', 'boolean'],
            'expires_at' => ['nullable', 'date'],
        ];

        return $request->validate($rules);
    }
}
