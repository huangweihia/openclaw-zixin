<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublishAudit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PublishAuditController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $q = PublishAudit::query()
            ->with(['user:id,name', 'auditor:id,name', 'userPost:id,title,type'])
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->when(in_array($status, ['pending', 'approved', 'rejected'], true), fn ($q2) => $q2->where('status', $status));

        return response()->json($q->paginate(30)->withQueryString());
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = PublishAudit::query()->findOrFail($id);
        $data = $request->validate([
            'status' => ['sometimes', Rule::in(['pending', 'approved', 'rejected'])],
            'reject_reason' => ['nullable', 'string'],
            'suggest' => ['nullable', 'string'],
            'priority' => ['sometimes', 'integer'],
            'auditor_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);
        $row->fill($data);
        if (array_key_exists('status', $data) && in_array($data['status'], ['approved', 'rejected'], true)) {
            $row->audited_at = now();
            if ($row->auditor_id === null) {
                $row->auditor_id = $request->user()->id;
            }
        }
        $row->save();

        return response()->json([
            'message' => '已更新',
            'publish_audit' => $row->fresh()->load(['user:id,name', 'auditor:id,name', 'userPost:id,title']),
        ]);
    }
}
