<?php

namespace App\Http\Controllers;

use App\Support\PricingConfig;
use Illuminate\View\View;

class VipController extends Controller
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function planCatalog(): array
    {
        return [
            'free' => [
                'key' => 'free',
                'name' => '免费版',
                'price' => '¥0',
                'period' => '永久',
                'features' => ['基础内容浏览', '社区互动', '案例 / 工具地图部分预览', '含广告'],
            ],
            'vip' => [
                'key' => 'vip',
                'name' => 'VIP',
                'price' => '¥29',
                'period' => '/ 月',
                'original_price' => '¥99',
                'original_price_yuan' => 99,
                'promo_label' => '限时特惠 · 72 小时',
                'spots_label' => '仅剩 200 席',
                'features' => ['案例库与工具地图全文', '运营 SOP / 资源合集', '无广告', '企业微信与邮件资讯', '投稿更高额度'],
            ],
            'svip' => [
                'key' => 'svip',
                'name' => 'SVIP',
                'price' => '¥99',
                'period' => '/ 月',
                'original_price' => '¥299',
                'original_price_yuan' => 299,
                'promo_label' => '早鸟价 · 本月截止',
                'spots_label' => '限 50 人 · 余量动态更新',
                'features' => ['含 VIP 全部权益', '定制采集与周报', '远程协助与优先审核', '自定义订阅与 SVIP 通道'],
            ],
        ];
    }

    /** 订单金额（元），与 product_id：1=vip，2=svip 对应 */
    public static function planAmountYuan(string $plan): ?float
    {
        return match ($plan) {
            'vip' => 29.00,
            'svip' => 99.00,
            default => null,
        };
    }

    public static function planProductId(string $plan): ?int
    {
        return match ($plan) {
            'vip' => 1,
            'svip' => 2,
            default => null,
        };
    }

    public function page(): View
    {
        $user = auth()->user();
        $role = $user?->role;
        $expiresAt = $user?->subscription_ends_at;
        $daysLeft = null;
        if ($expiresAt && $expiresAt->isFuture()) {
            $daysLeft = (int) now()->startOfDay()->diffInDays($expiresAt->copy()->startOfDay());
        }

        return view('vip.index', [
            'plans' => PricingConfig::catalogMerged(),
            'memberRole' => $role,
            'memberExpiresAt' => $expiresAt,
            'memberDaysLeft' => $daysLeft,
        ]);
    }

    public function pricing(): View
    {
        $user = auth()->user();
        $role = $user?->role;
        $expiresAt = $user?->subscription_ends_at;
        $daysLeft = null;
        if ($expiresAt && $expiresAt->isFuture()) {
            $daysLeft = (int) now()->startOfDay()->diffInDays($expiresAt->copy()->startOfDay());
        }

        return view('vip.pricing', [
            'plans' => PricingConfig::catalogMerged(),
            'memberRole' => $role,
            'memberExpiresAt' => $expiresAt,
            'memberDaysLeft' => $daysLeft,
        ]);
    }
}
