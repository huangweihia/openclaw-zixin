<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommentReport;
use App\Models\InboxNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommentReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $q = CommentReport::query()
            ->with(['user:id,name', 'comment:id,content'])
            ->orderByDesc('id')
            ->when(in_array($status, ['pending', 'processed', 'rejected'], true), fn ($q2) => $q2->where('status', $status));

        return response()->json($q->paginate(25)->withQueryString());
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = CommentReport::query()->findOrFail($id);
        $prevStatus = (string) $row->status;
        $wasProcessed = $row->processed_at !== null;

        $data = $request->validate([
            'status' => ['sometimes', Rule::in(['pending', 'processed', 'rejected'])],
            'admin_note' => ['nullable', 'string'],
        ]);
        $row->fill($data);
        if (array_key_exists('status', $data) && $data['status'] !== 'pending') {
            $row->processed_at = now();
            $row->processed_by = $request->user()->id;
        }
        $row->save();

        // 首次从 pending -> processed/rejected 时，给评论人和举报人发系统消息（写入 notifications 表）
        if (! $wasProcessed && $prevStatus === 'pending' && in_array($row->status, ['processed', 'rejected'], true)) {
            $row->loadMissing(['user:id,name', 'comment:id,user_id,content', 'comment.user:id,name']);

            $reporterId = (int) ($row->user_id ?? 0);
            $commentUserId = (int) ($row->comment?->user_id ?? 0);
            $commentSnippet = (string) ($row->comment?->content ?? '');
            if (mb_strlen($commentSnippet) > 60) {
                $commentSnippet = mb_substr($commentSnippet, 0, 60) . '…';
            }
            $adminNote = trim((string) ($row->admin_note ?? ''));

            // 给举报人
            if ($reporterId > 0) {
                InboxNotification::query()->create([
                    'user_id' => $reporterId,
                    'type' => 'system_announcement',
                    'title' => '你提交的评论举报已处理',
                    'content' => "处理结果：{$row->status}\n评论：{$commentSnippet}" . ($adminNote !== '' ? "\n管理员备注：{$adminNote}" : ''),
                    'action_url' => null,
                ]);
            }

            // 给评论人（避免自己举报自己时重复）
            if ($commentUserId > 0 && $commentUserId !== $reporterId) {
                InboxNotification::query()->create([
                    'user_id' => $commentUserId,
                    'type' => 'system_announcement',
                    'title' => '你的评论收到举报并已处理',
                    'content' => "处理结果：{$row->status}\n评论：{$commentSnippet}" . ($adminNote !== '' ? "\n管理员备注：{$adminNote}" : ''),
                    'action_url' => null,
                ]);
            }
        }

        return response()->json([
            'message' => '已更新',
            'report' => $row->fresh()->load(['user:id,name', 'comment:id,content']),
        ]);
    }
}
