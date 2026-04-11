<?php

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

];
