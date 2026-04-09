<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * 前台共享：站点名称、标语、联系方式等（来自 site_settings）。
 */
final class SiteViewComposer
{
    public const DEFAULT_SLOGAN = 'OpenClaw + AI 智能体：把分散的信息差转化为可交付的咨询洞察';

    public static function publicAssetUrl(string $stored): string
    {
        $s = trim($stored);
        if ($s === '') {
            return asset('favicon.svg');
        }
        if (preg_match('#^https?://#i', $s)) {
            return $s;
        }

        return asset(ltrim($s, '/'));
    }

    /**
     * @return array<string, string|null>
     */
    public static function branding(): array
    {
        if (! Schema::hasTable('site_settings')) {
            return [
                'site_name' => 'OpenClaw 智信',
                'site_slogan' => self::DEFAULT_SLOGAN,
                'site_description' => '',
                'contact_email' => '',
                'contact_wechat' => '',
                'footer_notice' => '',
                'site_logo_url' => '',
                'site_logo_href' => asset('favicon.svg'),
            ];
        }

        $rawLogo = SiteSetting::getValue('site_logo_url', '');

        return [
            'site_name' => SiteSetting::getValue('site_name', 'OpenClaw 智信'),
            'site_slogan' => SiteSetting::getValue('site_slogan', self::DEFAULT_SLOGAN) ?: self::DEFAULT_SLOGAN,
            'site_description' => SiteSetting::getValue('site_description', ''),
            'contact_email' => SiteSetting::getValue('contact_email', ''),
            'contact_wechat' => SiteSetting::getValue('contact_wechat', ''),
            'footer_notice' => SiteSetting::getValue('footer_notice', ''),
            'site_logo_url' => $rawLogo,
            'site_logo_href' => self::publicAssetUrl($rawLogo),
        ];
    }

    public function compose(View $view): void
    {
        $view->with('ocSite', self::branding());
    }
}
