<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommentReport;
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

        return response()->json([
            'message' => '已更新',
            'report' => $row->fresh()->load(['user:id,name', 'comment:id,content']),
        ]);
    }
}
