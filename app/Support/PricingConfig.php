<?php

namespace App\Support;

use App\Http\Controllers\VipController;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;

/**
 * 将「系统与站点」中的价格营销键合并进方案展示（倒计时、剩余名额等）。
 */
final class PricingConfig
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function catalogMerged(): array
    {
        $plans = VipController::planCatalog();
        if (! Schema::hasTable('site_settings')) {
            return $plans;
        }

        $vipPromo = trim((string) SiteSetting::getValue('pricing_vip_promo', ''));
        $svipPromo = trim((string) SiteSetting::getValue('pricing_svip_promo', ''));
        $vipSeats = trim((string) SiteSetting::getValue('pricing_vip_seats', ''));
        $svipSeats = trim((string) SiteSetting::getValue('pricing_svip_seats', ''));
        $vipDl = trim((string) SiteSetting::getValue('pricing_vip_deadline', ''));
        $svipDl = trim((string) SiteSetting::getValue('pricing_svip_deadline', ''));

        if ($vipPromo !== '') {
            $plans['vip']['promo_label'] = $vipPromo;
        }
        if ($svipPromo !== '') {
            $plans['svip']['promo_label'] = $svipPromo;
        }
        if ($vipSeats !== '') {
            $plans['vip']['spots_label'] = '仅剩 '.$vipSeats.' 席';
        }
        if ($svipSeats !== '') {
            $plans['svip']['spots_label'] = '限 '.$svipSeats.' 人 · 余量动态更新';
        }
        $plans['vip']['deadline_at'] = $vipDl !== '' ? $vipDl : null;
        $plans['svip']['deadline_at'] = $svipDl !== '' ? $svipDl : null;

        return $plans;
    }
}
