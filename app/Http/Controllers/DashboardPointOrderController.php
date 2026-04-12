<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PointPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DashboardPointOrderController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'point_package_id' => ['required', 'integer', 'exists:point_packages,id'],
        ]);

        $pkg = PointPackage::query()->whereKey($data['point_package_id'])->where('is_active', true)->firstOrFail();

        $orderNo = 'PT'.now()->format('YmdHis').Str::upper(Str::random(6));

        $order = Order::query()->create([
            'user_id' => $request->user()->id,
            'order_no' => $orderNo,
            'product_type' => 'point_package',
            'product_id' => $pkg->id,
            'amount' => $pkg->price_yuan,
            'status' => 'pending',
            'remark' => 'point_package:'.$pkg->points_amount,
        ]);

        return redirect()->route('payments.result', ['order_no' => $order->order_no]);
    }
}
