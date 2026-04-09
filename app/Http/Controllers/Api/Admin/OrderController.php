<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $perPage = (int) $request->query('per_page', 25);
        $perPage = max(10, min($perPage, 100));
        $q = trim((string) $request->query('q', ''));

        $orders = Order::query()
            ->with('user:id,name,email')
            ->when(in_array($status, ['pending', 'paid', 'failed', 'refunded'], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('order_no', 'like', '%'.$q.'%')
                        ->orWhere('payment_id', 'like', '%'.$q.'%')
                        ->orWhereHas('user', function ($uq) use ($q) {
                            $uq->where('email', 'like', '%'.$q.'%')
                                ->orWhere('name', 'like', '%'.$q.'%');
                        });
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($orders);
    }

    public function show(Order $order): JsonResponse
    {
        return response()->json([
            'order' => $order->load('user:id,name,email'),
        ]);
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $data = $request->validate([
            'status' => ['sometimes', Rule::in(['pending', 'paid', 'failed', 'refunded'])],
            'remark' => ['nullable', 'string'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'payment_id' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['nullable', 'date'],
        ]);
        $order->fill($data);
        if (($data['status'] ?? null) === 'paid' && $order->paid_at === null && empty($data['paid_at'])) {
            $order->paid_at = now();
        }
        $order->save();

        return response()->json([
            'message' => '订单已更新',
            'order' => $order->fresh()->load('user:id,name,email'),
        ]);
    }
}
