<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * 小程序订阅消息：下发模板 ID 供 wx.requestSubscribeMessage 使用。
 */
class WeChatMiniSubscribeController extends Controller
{
    public function config(): JsonResponse
    {
        $raw = config('wechat.mini_subscribe_template_ids', []);
        $ids = is_array($raw)
            ? array_values(array_filter(array_map('trim', array_map('strval', $raw))))
            : array_values(array_filter(array_map('trim', explode(',', (string) $raw))));
        // 微信单次最多请求 3 个模板
        $ids = array_slice($ids, 0, 3);

        return response()->json([
            'success' => true,
            'template_ids' => $ids,
            'hint' => '在「会员与订阅」页点击开启提醒，授权后服务端可在会员临近到期时尝试推送（一次性模板每次授权可发一条；需在公众平台配置模板并填写 WECHAT_MINI_SUBSCRIBE_TEMPLATE_IDS）。',
        ]);
    }
}
