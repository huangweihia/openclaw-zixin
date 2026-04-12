<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Filament 面板使用独立 Session Cookie，与前台 web 会话分离（一处登出不牵连另一处）。
 * 须在 StartSession 之前注册。
 */
class ConfigureFilamentSessionCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        $cookie = trim((string) config('admin.session_cookie', ''));
        if ($cookie !== '') {
            config(['session.cookie' => $cookie]);
        }

        return $next($request);
    }
}
