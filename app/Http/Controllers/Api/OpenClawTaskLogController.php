<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OpenclawTaskLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OpenClawTaskLogController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $token = $request->header('X-API-Token');
        if ($token !== config('services.openclaw.token')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized: Invalid API Token'], 401);
        }

        $validated = $request->validate([
            'task_name' => ['required', 'string', 'max:255'],
            'task_id' => ['nullable', 'string', 'max:255'],
            'task_type' => ['required', 'in:ai_content,svip_subscription,svip_content,daily_news'],
            'status' => ['required', 'in:success,error,timeout,skipped'],
            'duration_ms' => ['nullable', 'integer', 'min:0'],
            'data_summary' => ['nullable', 'array'],
            'total_items' => ['nullable', 'integer', 'min:0'],
            'success_count' => ['nullable', 'integer', 'min:0'],
            'failed_count' => ['nullable', 'integer', 'min:0'],
            'skipped_count' => ['nullable', 'integer', 'min:0'],
            'api_endpoint' => ['nullable', 'string', 'max:500'],
            'push_status' => ['nullable', 'in:success,failed,not_attempted'],
            'push_response' => ['nullable', 'string'],
            'error_message' => ['nullable', 'string'],
            'error_details' => ['nullable', 'string'],
            'started_at' => ['required', 'date'],
            'finished_at' => ['nullable', 'date'],
        ]);

        $log = OpenclawTaskLog::query()->create([
            ...$validated,
            'push_status' => $validated['push_status'] ?? OpenclawTaskLog::PUSH_NOT_ATTEMPTED,
            'total_items' => $validated['total_items'] ?? 0,
            'success_count' => $validated['success_count'] ?? 0,
            'failed_count' => $validated['failed_count'] ?? 0,
            'skipped_count' => $validated['skipped_count'] ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => '日志已记录',
            'log_id' => $log->id,
        ]);
    }
}

