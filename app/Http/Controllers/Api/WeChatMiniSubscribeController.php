<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\WeChatMiniSubscribeTemplateIds;
use Illuminate\Http\JsonResponse;

/**
 * 小程序订阅消息：下发模板 ID 供 wx.requestSubscribeMessage 使用。
 */
class WeChatMiniSubscribeController extends Controller
{
    public function config(): JsonResponse
    {
        $ids = WeChatMiniSubscribeTemplateIds::forRequestSubscribeMessage();

        return response()->json([
            'success' => true,
            'template_ids' => $ids,
            'hint' => '在会员页开启提醒前需有模板 ID：优先读取服务器 .env 的 WECHAT_MINI_SUBSCRIBE_TEMPLATE_IDS；若为空则读后台「站点设置 → 微信小程序订阅消息模板 ID」。亦可在小程序 utils/config.js 的 SUBSCRIBE_TEMPLATE_IDS_FALLBACK 填写兜底（逗号分隔，最多 3 个）。公众平台须已添加对应「一次性订阅」模板。',
        ]);
    }
}
