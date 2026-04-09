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
        $perPage = (int) $request->query('per_page', 25);
        $perPage = max(10, min($perPage, 100));
        $q = trim((string) $request->query('q', ''));

        $logs = EmailLog::query()
            ->with('user:id,name,email')
            ->when(in_array($status, ['pending', 'sent', 'failed'], true), function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($sub) use ($q) {
                    $sub->where('to', 'like', '%'.$q.'%')
                        ->orWhere('subject', 'like', '%'.$q.'%')
                        ->orWhere('template_key', 'like', '%'.$q.'%');
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage)
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
