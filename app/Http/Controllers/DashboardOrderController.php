<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardOrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('dashboard-orders', compact('orders'));
    }

    public function requestRefund(Request $request, Order $order): RedirectResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);
        abort_unless($order->status === 'paid', 400);

        if ($order->refund_requested_at !== null) {
            return back()->with('error', '该订单已提交过退款申请，请耐心等待处理。');
        }

        $paidAt = $order->paid_at ?? $order->created_at;
        if ($paidAt === null || $paidAt->lt(now()->subDays(7))) {
            return back()->with('error', '仅支持支付成功后 7 日内申请退款。');
        }

        $order->forceFill(['refund_requested_at' => now()])->save();

        return back()->with('success', '退款申请已提交，客服将尽快处理。');
    }
}
