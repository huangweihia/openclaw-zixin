<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Docker / Nginx 反代到 php-fpm 时，须信任反代，否则无法识别 X-Forwarded-Proto，
     * Laravel 仍按「http」生成 URL，浏览器在 HTTPS 下会拦截 Livewire/Filament 的 http 脚本（混合内容），
     * 登录表单无法被 Livewire 接管 → 整页 POST /admin/login → 405。
     *
     * 公网若 PHP 仅能从内网 Nginx 访问，用 '*' 一般可接受；否则改为具体反代 IP 段或 TRUSTED_PROXIES 环境变量（逗号分隔）。
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
