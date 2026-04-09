<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\OpenclawTaskLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

final class OpenclawTaskLogAdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! Schema::hasTable('openclaw_task_logs')) {
            return response()->json([
                'data' => [],
                'current_page' => 1,
                'last_page' => 1,
                'total' => 0,
                'message' => 'openclaw_task_logs 表不存在，请先执行 migrate',
            ], 200);
        }

        $q = trim((string) $request->query('q', ''));
        $type = trim((string) $request->query('task_type', ''));
        $status = trim((string) $request->query('status', ''));
        $pushStatus = trim((string) $request->query('push_status', ''));
        $from = trim((string) $request->query('from', ''));
        $to = trim((string) $request->query('to', ''));

        $query = OpenclawTaskLog::query();

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('task_name', 'like', "%{$q}%")
                    ->orWhere('task_id', 'like', "%{$q}%")
                    ->orWhere('api_endpoint', 'like', "%{$q}%")
                    ->orWhere('error_message', 'like', "%{$q}%");
            });
        }
        if ($type !== '') {
            $query->where('task_type', $type);
        }
        if ($status !== '') {
            $query->where('status', $status);
        }
        if ($pushStatus !== '') {
            $query->where('push_status', $pushStatus);
        }
        if ($from !== '') {
            $query->whereDate('started_at', '>=', $from);
        }
        if ($to !== '') {
            $query->whereDate('started_at', '<=', $to);
        }

        $rows = $query
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($rows);
    }

    public function show(int $id): JsonResponse
    {
        $row = OpenclawTaskLog::query()->findOrFail($id);

        return response()->json(['log' => $row]);
    }

    public function destroy(int $id): JsonResponse
    {
        $row = OpenclawTaskLog::query()->findOrFail($id);
        $row->delete();

        return response()->json(['message' => '已删除']);
    }

    public function stats(Request $request): JsonResponse
    {
        if (! Schema::hasTable('openclaw_task_logs')) {
            return response()->json(['stats' => []]);
        }

        $days = (int) $request->query('days', 7);
        $days = max(1, min(90, $days));

        $from = now()->subDays($days - 1)->startOfDay();

        $stats = OpenclawTaskLog::query()
            ->where('started_at', '>=', $from)
            ->selectRaw("DATE(started_at) as d, task_type, status, COUNT(*) as n")
            ->groupBy('d', 'task_type', 'status')
            ->orderBy('d')
            ->get();

        return response()->json(['stats' => $stats]);
    }
}

