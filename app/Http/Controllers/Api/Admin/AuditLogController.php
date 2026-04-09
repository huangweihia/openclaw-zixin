<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $action = $request->query('action');
        $q = AuditLog::query()
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->when(is_string($action) && $action !== '', fn ($q2) => $q2->where('action', $action));

        return response()->json($q->paginate(40)->withQueryString());
    }
}
