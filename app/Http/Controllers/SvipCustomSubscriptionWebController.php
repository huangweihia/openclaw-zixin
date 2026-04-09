<?php

namespace App\Http\Controllers;

use App\Models\SvipCustomSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * SVIP 定制订阅（前台）：与表 svip_custom_subscriptions 对齐；SVIP 与管理员可访问（管理员用于验收与演示）。
 */
class SvipCustomSubscriptionWebController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->role === 'svip' || $request->user()->isAdmin(), 403);

        $items = SvipCustomSubscription::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->paginate(15);

        return view('svip-subscriptions.index', ['items' => $items]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'svip' || $request->user()->isAdmin(), 403);

        $data = $request->validate([
            'plan_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:4000'],
            'delivery_frequency' => ['nullable', 'string', 'max:40'],
            'preferred_send_time' => ['nullable', 'string', 'max:40'],
            'delivery_channel' => ['nullable', 'string', 'max:40'],
        ]);

        SvipCustomSubscription::query()->create([
            'user_id' => $request->user()->id,
            'plan_name' => $data['plan_name'],
            'description' => $data['description'] ?? null,
            'delivery_frequency' => $data['delivery_frequency'] ?? null,
            'preferred_send_time' => $data['preferred_send_time'] ?? null,
            'delivery_channel' => $data['delivery_channel'] ?? null,
            'amount' => 0,
            'duration_days' => 0,
            'services' => [],
            'status' => 'pending',
            'started_at' => null,
            'expires_at' => null,
        ]);

        return back()->with('success', '已提交定制需求，运营将与你联系确认。');
    }

    public function destroy(Request $request, SvipCustomSubscription $svipCustomSubscription): RedirectResponse
    {
        abort_unless($request->user()->role === 'svip' || $request->user()->isAdmin(), 403);
        abort_unless((int) $svipCustomSubscription->user_id === (int) $request->user()->id, 403);

        $svipCustomSubscription->delete();

        return back()->with('success', '已删除该条记录');
    }
}
