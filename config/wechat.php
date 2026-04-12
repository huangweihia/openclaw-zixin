<?php

$subscribeIds = array_values(array_filter(array_map('trim', explode(',', (string) env('WECHAT_MINI_SUBSCRIBE_TEMPLATE_IDS', '')))));

$expiryMapRaw = env('WECHAT_MINI_SUBSCRIBE_EXPIRY_FIELD_MAP', '');
$expiryMap = is_string($expiryMapRaw) && $expiryMapRaw !== '' ? json_decode($expiryMapRaw, true) : null;
if (! is_array($expiryMap)) {
    $expiryMap = [
        // 与公众平台「会员到期通知」类模板常见字段对齐；若报 47003 请改为你的模板详情里的 keyword
        'thing1' => 'member_name',
        'time2' => 'expires_at',
        'number3' => 'days_left',
        'thing4' => 'rights_hint',
    ];
}

return [

    /*
    |--------------------------------------------------------------------------
    | 微信小程序（用于 jscode2session、小程序登录）
    |--------------------------------------------------------------------------
    |
    | 在 .env 中配置 WECHAT_MINI_APP_ID、WECHAT_MINI_APP_SECRET。
    | AppSecret 仅服务端使用，切勿写入小程序前端代码。
    |
    */

    'mini_app_id' => env('WECHAT_MINI_APP_ID', ''),

    'mini_app_secret' => env('WECHAT_MINI_APP_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | 订阅消息（公众平台 → 订阅消息 → 我的模板）
    |--------------------------------------------------------------------------
    |
    | WECHAT_MINI_SUBSCRIBE_TEMPLATE_IDS：逗号分隔，供客户端 wx.requestSubscribeMessage（最多 3 个）。
    | WECHAT_MINI_SUBSCRIBE_MEMBERSHIP_EXPIRY_TEMPLATE_ID：服务端 send 使用的模板；留空则取上一项第一个 ID。
    | WECHAT_MINI_SUBSCRIBE_EXPIRY_FIELD_MAP：JSON，键为模板 keyword（thing1/time2/number3），值为 member_name|expires_at|days_left|rights_hint
    |
    */

    'mini_subscribe_template_ids' => $subscribeIds,

    'mini_subscribe_membership_expiry_template_id' => trim((string) env('WECHAT_MINI_SUBSCRIBE_MEMBERSHIP_EXPIRY_TEMPLATE_ID', '')),

    'mini_subscribe_expiry_field_map' => $expiryMap,

    /** 跳转小程序路径（订阅消息点击后打开） */
    'mini_subscribe_jump_page' => env('WECHAT_MINI_SUBSCRIBE_JUMP_PAGE', 'pages/vip/vip'),

    /** developer | trial | formal */
    'mini_subscribe_miniprogram_state' => env('WECHAT_MINI_SUBSCRIBE_MINIPROGRAM_STATE', 'formal'),

    /** 到期前 N 天内尝试发送（由定时任务扫描） */
    'mini_subscribe_expiry_days_before' => (int) env('WECHAT_MINI_SUBSCRIBE_EXPIRY_DAYS_BEFORE', 3),
];
