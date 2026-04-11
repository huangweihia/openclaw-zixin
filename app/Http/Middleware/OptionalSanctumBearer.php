<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * 若请求携带 Bearer Token，则尝试解析为 Sanctum 用户，不抛 401。
 * 用于 GET /api/skins 等「匿名可访问、登录后数据更完整」的接口。
 */
class OptionalSanctumBearer
{
    public function handle(Request $request, Closure $next): Response
    {
        $plain = $request->bearerToken();
        if (! is_string($plain) || $plain === '') {
            return $next($request);
        }

        $accessToken = PersonalAccessToken::findToken($plain);
        if ($accessToken && $accessToken->tokenable) {
            Auth::guard('sanctum')->setUser($accessToken->tokenable);
            Auth::shouldUse('sanctum');
        }

        return $next($request);
    }
}
