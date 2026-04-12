<?php

namespace App\Support;

use App\Models\Article;
use App\Models\User;

/**
 * 前台会员门槛页的遮罩文案与跳转（定价锚点 / 登录回跳）。
 */
final class SiteGateMask
{
    /**
     * 文章：VIP 正文 vs is_vip_only（SVIP）正文。
     *
     * @return array{level: string, title: string, desc: string, cta: string, href: string}
     */
    public static function forArticle(Article $article, ?User $user, string $returnUrl): array
    {
        $p = route('pricing');
        if ($article->is_vip_only) {
            return [
                'level' => 'svip',
                'title' => 'SVIP 专属正文',
                'desc' => '升级 SVIP 后可阅读全文。',
                'cta' => $user ? '升级 SVIP' : '登录后升级 SVIP',
                'href' => $user ? ($p.'#plan-svip') : route('login', ['return' => $returnUrl]),
            ];
        }

        return [
            'level' => 'vip',
            'title' => 'VIP 专属正文',
            'desc' => '开通 VIP 后可阅读全文。',
            'cta' => $user ? '解锁 VIP' : '登录后开通',
            'href' => $user ? ($p.'#plan-vip') : route('login', ['return' => $returnUrl]),
        ];
    }

    /**
     * 案例 / 工具 / SOP / 投稿等：仅 VIP 门槛（visibility = vip）。
     *
     * @return array{level: string, title: string, desc: string, cta: string, href: string}
     */
    public static function forVipExclusive(?User $user, string $returnUrl): array
    {
        $p = route('pricing');

        return [
            'level' => 'vip',
            'title' => 'VIP 专属内容',
            'desc' => '开通 VIP 后可阅读全文。',
            'cta' => $user ? '解锁 VIP' : '登录后开通',
            'href' => $user ? ($p.'#plan-vip') : route('login', ['return' => $returnUrl]),
        ];
    }
}
