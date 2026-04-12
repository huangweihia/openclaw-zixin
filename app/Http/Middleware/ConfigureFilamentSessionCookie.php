<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Filament 可选独立 Session Cookie（仅「双域名拆分」时与 SESSION_COOKIE 区分）。
 * 须在 EncryptCookies 与 StartSession 之前执行，以便 Cookie 加解密与会话名一致。
 *
 * 路径 / IP 同域：`config('admin.session_cookie')` 恒为空，并在响应中清除历史 Filament Cookie，
 * 避免浏览器同时携带两套 Session → Livewire 419。
 */
class ConfigureFilamentSessionCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        $cookie = trim((string) config('admin.session_cookie', ''));
        if ($cookie !== '' && $this->shouldUseFilamentSession($request)) {
            config(['session.cookie' => $cookie]);
        }

        $response = $next($request);

        if ($cookie === '') {
            foreach (config('admin.obsolete_session_cookies_to_expire', []) as $name) {
                $name = trim((string) $name);
                if ($name === '' || $name === config('session.cookie')) {
                    continue;
                }
                // 须在 $next 之后直接挂到本响应上；queue 若晚于 AddQueuedCookies 出站则不会写入
                $response = $response->withCookie(cookie()->forget($name));
            }
        }

        return $response;
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
