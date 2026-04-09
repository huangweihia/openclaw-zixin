<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvoiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvoiceRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $q = InvoiceRequest::query()
            ->with(['user:id,name,email', 'order:id,order_no'])
            ->orderByDesc('id')
            ->when(in_array($status, ['pending', 'issued', 'rejected'], true), fn ($q2) => $q2->where('status', $status));

        return response()->json($q->paginate(25)->withQueryString());
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = InvoiceRequest::query()->findOrFail($id);
        $data = $request->validate([
            'status' => ['sometimes', Rule::in(['pending', 'issued', 'rejected'])],
            'admin_note' => ['nullable', 'string'],
            'invoice_file' => ['nullable', 'string', 'max:255'],
        ]);
        $row->fill($data);
        if (array_key_exists('status', $data) && in_array($data['status'], ['issued', 'rejected'], true)) {
            $row->processed_at = now();
        }
        $row->save();

        return response()->json([
            'message' => '已更新',
            'invoice_request' => $row->fresh()->load(['user:id,name', 'order:id,order_no']),
        ]);
    }
}
