<?php

namespace App\Http\Controllers;

use App\Models\SvipCustomSubscription;
use App\Models\SkinConfig;
use App\Models\UserSkin;
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

    /**
     * SVIP 皮肤定制：每个自然月仅 1 次，且仅创建者可用。
     */
    public function customizeSkin(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && ($user->role === 'svip' || $user->isAdmin()), 403);

        if ($user->role === 'svip' && (! $user->subscription_ends_at || $user->subscription_ends_at->isPast())) {
            return back()->withErrors(['skin_name' => 'SVIP 已到期，续费后可继续定制皮肤。']);
        }

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $count = SkinConfig::query()
            ->where('owner_user_id', $user->id)
            ->where('custom_source', 'svip_custom')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->count();
        if ($count >= 1) {
            return back()->withErrors(['skin_name' => '本月已定制过 1 套皮肤，请下月再试。']);
        }

        $data = $request->validate([
            'skin_name' => ['required', 'string', 'max:60'],
            'skin_primary' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'skin_secondary' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'skin_accent' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'skin_bg' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $code = 'svip-u'.$user->id.'-'.now()->format('Ym').'-'.substr(md5((string) microtime(true)), 0, 6);
        $skin = SkinConfig::query()->create([
            'name' => $data['skin_name'],
            'code' => $code,
            'owner_user_id' => $user->id,
            'description' => 'SVIP 用户定制皮肤',
            'type' => 'svip',
            'is_private' => true,
            'custom_source' => 'svip_custom',
            'preview_image' => null,
            'css_variables' => [
                '--primary-color' => $data['skin_primary'],
                '--secondary-color' => $data['skin_secondary'],
                '--accent-color' => $data['skin_accent'],
                '--bg-color' => $data['skin_bg'],
                '--gradient-primary' => 'linear-gradient(135deg, '.$data['skin_primary'].' 0%, '.$data['skin_secondary'].' 100%)',
            ],
            'sort' => 999,
            'is_active' => true,
        ]);

        UserSkin::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['skin_id' => $skin->id, 'activated_at' => now()]
        );

        return back()->with('success', '定制皮肤已创建并自动启用（仅你本人可见/可用）。');
    }
}
