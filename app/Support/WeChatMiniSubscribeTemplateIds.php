<?php

namespace App\Support;

use App\Models\SiteSetting;

/**
 * 小程序 requestSubscribeMessage 使用的模板 ID：优先 .env，为空则用站点设置（便于无 SSH 时配置）。
 */
final class WeChatMiniSubscribeTemplateIds
{
    /**
     * @return list<string>
     */
    public static function forRequestSubscribeMessage(): array
    {
        $fromEnv = config('wechat.mini_subscribe_template_ids', []);
        if (! is_array($fromEnv)) {
            $fromEnv = [];
        }
        $ids = array_values(array_filter(array_map('trim', array_map('strval', $fromEnv))));
        if ($ids !== []) {
            return array_slice($ids, 0, 3);
        }

        $raw = (string) SiteSetting::getValue('wechat_mini_subscribe_template_ids', '');
        $fromDb = array_values(array_filter(array_map('trim', explode(',', $raw))));

        return array_slice($fromDb, 0, 3);
    }
}
