<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PointPackage;
use App\Services\PointsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function confirm(Request $request): View
    {
        $plan = (string) $request->query('plan', 'vip');
        $catalog = VipController::planCatalog();
        if (! isset($catalog[$plan])) {
            $plan = 'vip';
        }

        return view('payments.confirm', [
            'planKey' => $plan,
            'plan' => $catalog[$plan],
            'payable' => in_array($plan, ['vip', 'svip'], true),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plan' => ['required', 'in:vip,svip'],
        ]);

        $plan = $data['plan'];
        $amount = VipController::planAmountYuan($plan);
        $productId = VipController::planProductId($plan);
        if ($amount === null || $productId === null) {
            abort(422, '无效的套餐');
        }

        $orderNo = 'OC'.now()->format('YmdHis').Str::upper(Str::random(6));

        $order = Order::query()->create([
            'user_id' => $request->user()->id,
            'order_no' => $orderNo,
            'product_type' => 'subscription_plan',
            'product_id' => $productId,
            'amount' => $amount,
            'status' => 'pending',
            'remark' => 'plan:'.$plan,
        ]);

        return redirect()->route('payments.result', ['order_no' => $order->order_no]);
    }

    public function result(Request $request): View
    {
        $orderNo = $request->query('order_no', '');
        $order = Order::query()
            ->where('order_no', $orderNo)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $orderSummary = null;
        if ($order->product_type === 'point_package' && Schema::hasTable('point_packages')) {
            $pkg = PointPackage::query()->find((int) $order->product_id);
            $orderSummary = $pkg ? ($pkg->name.' · '.$pkg->points_amount.' 积分') : '积分套餐';
        }

        return view('payments.result', [
            'order' => $order,
            'simulateEnabled' => (bool) config('openclaw.payment_simulate'),
            'planKey' => $order->planKeyFromProduct(),
            'orderSummary' => $orderSummary,
        ]);
    }

    public function simulatePaid(Request $request, Order $order): RedirectResponse
    {
        if (! config('openclaw.payment_simulate')) {
            abort(404);
        }

        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);
        abort_unless($order->status === 'pending', 400);

        $user = $request->user();

        if ($order->product_type === 'point_package') {
            abort_unless(Schema::hasTable('point_packages'), 400);
            $pkg = PointPackage::query()->whereKey((int) $order->product_id)->where('is_active', true)->first();
            abort_unless($pkg !== null, 400);

            DB::transaction(function () use ($order, $user, $pkg) {
                $order->forceFill([
                    'status' => 'paid',
                    'payment_method' => 'demo_simulate',
                    'paid_at' => now(),
                ])->save();

                PointsService::earn(
                    $user,
                    (int) $pkg->points_amount,
                    'point_purchase',
                    '购买积分套餐：'.$pkg->name,
                    Order::class,
                    (int) $order->id
                );
            });

            return redirect()
                ->route('payments.result', ['order_no' => $order->order_no])
                ->with('success', '演示支付已完成，积分已到账（非真实扣款）。');
        }

        $planKey = $order->planKeyFromProduct();
        abort_unless($planKey !== null, 400);

        DB::transaction(function () use ($order, $user, $planKey) {
            $order->forceFill([
                'status' => 'paid',
                'payment_method' => 'demo_simulate',
                'paid_at' => now(),
            ])->save();

            if ($user->role !== 'admin') {
                $user->role = $planKey === 'svip' ? 'svip' : 'vip';
            }
            $user->subscription_ends_at = $user->subscription_ends_at && $user->subscription_ends_at->isFuture()
                ? $user->subscription_ends_at->copy()->addMonth()
                : now()->addMonth();
            $user->save();
        });

        return redirect()
            ->route('payments.result', ['order_no' => $order->order_no])
            ->with('success', '演示支付已完成，会员权益已更新（非真实扣款）。');
    }
}
