<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Filament 可选独立 Session Cookie（与前台 SESSION_COOKIE 区分）。
 * 须在 EncryptCookies 与 StartSession 之前执行，以便 Cookie 加解密与会话名一致。
 *
 * 若配置了独立 Cookie 但未命中本中间件条件，Livewire POST 会落到默认会话 → 419。
 */
class ConfigureFilamentSessionCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        $cookie = trim((string) config('admin.session_cookie', ''));
        if ($cookie === '') {
            return $next($request);
        }

        if ($this->shouldUseFilamentSession($request)) {
            config(['session.cookie' => $cookie]);
        }

        return $next($request);
    }

    private function shouldUseFilamentSession(Request $request): bool
    {
        $adminDomain = trim((string) config('admin.domain', ''));
        $frontDomain = trim((string) config('app.front_domain', ''));

        if ($adminDomain !== '' && $frontDomain !== '') {
            $host = strtolower($request->getHost());
            $adminHost = strtolower((string) preg_replace('/:\d+$/', '', $adminDomain));

            if ($adminHost !== '' && ($host === $adminHost || str_ends_with($host, '.'.$adminHost))) {
                return true;
            }

            if ($request->is('livewire/*')) {
                if ($this->refererAdminHostMatches($request, $adminHost)) {
                    return true;
                }

                // Referer 常被代理/浏览器策略去掉；若请求里已有后台专用 Cookie，仍用同一 Session，避免 419
                return $this->requestHasFilamentSessionCookie($request);
            }

            return false;
        }

        $prefix = trim((string) config('admin.path_prefix', 'admin'), '/');
        $adminBase = $prefix === '' ? 'admin' : $prefix;
        $path = $request->path();

        if ($path === $adminBase || str_starts_with($path, $adminBase.'/')) {
            return true;
        }

        if ($request->is('livewire/*')) {
            // 同域路径型后台：全站仅 Filament 使用 Livewire，不再依赖 Referer/Cookie 猜测，避免 419
            return true;
        }

        return false;
    }

    /**
     * 判断是否已携带 Filament 专用 Session Cookie（与 config admin.session_cookie 一致）。
     */
    private function requestHasFilamentSessionCookie(Request $request): bool
    {
        $name = trim((string) config('admin.session_cookie', ''));
        if ($name === '') {
            return false;
        }

        $cookieHeader = (string) $request->headers->get('Cookie', '');

        return str_contains($cookieHeader, $name.'=');
    }

    private function refererAdminHostMatches(Request $request, string $adminHost): bool
    {
        if ($adminHost === '') {
            return false;
        }
        $ref = (string) $request->headers->get('referer', '');
        if ($ref === '') {
            return false;
        }
        $refHost = strtolower((string) (parse_url($ref, PHP_URL_HOST) ?? ''));

        return $refHost === $adminHost || ($refHost !== '' && str_ends_with($refHost, '.'.$adminHost));
    }
}
