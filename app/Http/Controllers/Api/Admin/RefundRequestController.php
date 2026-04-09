<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RefundRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $q = RefundRequest::query()
            ->with(['user:id,name,email', 'order:id,order_no,amount'])
            ->orderByDesc('id')
            ->when(in_array($status, ['pending', 'approved', 'rejected', 'completed'], true), fn ($q2) => $q2->where('status', $status));

        return response()->json($q->paginate(25)->withQueryString());
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = RefundRequest::query()->findOrFail($id);
        $data = $request->validate([
            'status' => ['sometimes', Rule::in(['pending', 'approved', 'rejected', 'completed'])],
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
            'refund_request' => $row->fresh()->load(['user:id,name', 'order:id,order_no']),
        ]);
    }
}
