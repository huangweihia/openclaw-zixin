<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 演示支付（无真实微信/支付宝网关时）
    |--------------------------------------------------------------------------
    |
    | 生产环境务必设为 false，仅接好真实支付后再开放正式下单。
    |
    */

    'payment_simulate' => ! in_array(
        strtolower((string) env('PAYMENT_SIMULATE', 'true')),
        ['false', '0', 'no', 'off'],
        true
    ),

    /*
    |--------------------------------------------------------------------------
    | 静态资源 URL 版本
    |--------------------------------------------------------------------------
    |
    | 默认关闭按文件 mtime 拼接 ?v=：在 Docker Desktop（尤其 Windows 挂载卷）上，
    | 每个请求多次 filemtime 可能导致整页 TTFB 数秒。需要强刷缓存时把
    | PUBLIC_ASSET_FILEMTIME 设为 true，或提高 ASSET_VERSION。
    |
    */

    'public_asset_use_filemtime' => filter_var(
        env('PUBLIC_ASSET_FILEMTIME', false),
        FILTER_VALIDATE_BOOLEAN
    ),

    'public_asset_version' => env('ASSET_VERSION', '1'),

];
