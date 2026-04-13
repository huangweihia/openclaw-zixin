<?php

$adminDomain = trim((string) env('ADMIN_DOMAIN', ''));
$frontDomain = trim((string) env('APP_FRONT_DOMAIN', ''));
$filamentCookie = trim((string) env('FILAMENT_SESSION_COOKIE', env('ADMIN_SESSION_COOKIE', '')));
/** 仅当同时配置前台域 + 后台域时，才允许独立 Filament Session；路径/IP 同域一律与 SESSION_COOKIE 共用。 */
$dualHostSplit = $adminDomain !== '' && $frontDomain !== '';
$useFilamentCookie = $dualHostSplit;
$filamentCookieName = $filamentCookie !== '' ? $filamentCookie : 'oc_filament_session';

return [

    /*
    |--------------------------------------------------------------------------
    | 后台独立域名（可选）
    |--------------------------------------------------------------------------
    |
    | 若设置（如 admin.localhost），后台路由仅在该 Host 下注册，路径为 /dashboard、/users…
    | 不再使用 /admin 前缀。此时必须在 .env 同时设置 APP_FRONT_DOMAIN，前台路由仅在该域名下注册。
    | 留空则沿用路径前缀 ADMIN_PATH_PREFIX（默认 admin），即 /admin/dashboard。
    |
    */
    'domain' => env('ADMIN_DOMAIN'),

    'path_prefix' => trim((string) env('ADMIN_PATH_PREFIX', 'admin'), '/'),

    /*
    |--------------------------------------------------------------------------
    | 后台独立 Session Cookie（仅双域名拆分时生效）
    |--------------------------------------------------------------------------
    |
    | 路径型 / 纯 IP 同域访问时，固定与 SESSION_COOKIE 共用。
    | 仅双域名拆分（ADMIN_DOMAIN + APP_FRONT_DOMAIN 同时配置）时，后台使用独立 Cookie。
    |
    */
    'session_cookie' => $useFilamentCookie ? $filamentCookieName : '',

    /*
    | 与前台共用 Session 时，在响应中排队清除这些 Cookie 名，消除双会话残留。
    |
    | @var array<int, string>
    */
    'obsolete_session_cookies_to_expire' => $useFilamentCookie
        ? []
        : array_values(array_unique(array_filter([
            $filamentCookie,
            'oc_filament_session',
        ]))),

];
