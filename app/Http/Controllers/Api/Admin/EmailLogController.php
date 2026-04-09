<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');

        $logs = EmailLog::query()
            ->with('user:id,name,email')
            ->when(in_array($status, ['pending', 'sent', 'failed'], true), function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return response()->json($logs);
    }

    public function show(int $id): JsonResponse
    {
        $log = EmailLog::query()
            ->with('user:id,name,email')
            ->findOrFail($id);

        return response()->json($log);
    }
}
