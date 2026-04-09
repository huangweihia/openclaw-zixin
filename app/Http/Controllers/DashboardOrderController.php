<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SiteTestimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

    public function review(Request $request, Order $order): RedirectResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);
        abort_unless($order->status === 'paid', 400);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $u = $request->user();
        $planKey = $order->planKeyFromProduct();
        $caption = match ($planKey) {
            'svip' => 'SVIP · 订单 '.$order->order_no,
            'vip' => 'VIP · 订单 '.$order->order_no,
            default => '订单 '.$order->order_no,
        };

        $name = (string) ($u->name ?? '用户');
        $displayName = Str::limit($name, 1, '***');
        $avatarInitial = mb_substr($name, 0, 1) ?: '用';

        SiteTestimonial::query()->create([
            'display_name' => $displayName,
            'caption' => $caption,
            'body' => $data['body'],
            'rating' => (int) $data['rating'],
            'avatar_initial' => $avatarInitial,
            'gradient_from' => 'from-blue-400',
            'gradient_to' => 'to-blue-600',
            'sort_order' => 0,
            'is_published' => false, // 默认进池子但不发布，后台审核后发布到首页
        ]);

        return back()->with('success', '评价已提交，审核通过后会展示在首页评价区。');
    }
}

