<?php

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
    | 后台独立 Session Cookie 名（与 SESSION_COOKIE 区分）。
    | 留空则与前台共用同一会话（推荐，避免 Filament/Livewire 与 EncryptCookies 顺序导致 419）。
    | 必须前后台会话隔离时再设置 FILAMENT_SESSION_COOKIE。
    */
    'session_cookie' => env('FILAMENT_SESSION_COOKIE', env('ADMIN_SESSION_COOKIE', '')),

];
