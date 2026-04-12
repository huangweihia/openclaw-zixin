<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Filament 面板使用独立 Session Cookie，与前台 web 会话分离。
 * 必须在 StartSession 之前执行。
 *
 * Livewire 的 POST /livewire/update 走全局 web 中间件，若不切换 Cookie，
 * 会与 Filament 页的会话不一致，导致后台登录/操作出现 419（CSRF）。
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
            $ref = (string) $request->headers->get('referer', '');
            if ($ref !== '' && str_contains($ref, '/'.$adminBase)) {
                return true;
            }

            return $this->requestHasFilamentSessionCookie($request);
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
