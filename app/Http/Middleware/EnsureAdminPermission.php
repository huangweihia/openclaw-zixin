<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EnsureAdminPermission
{
    /**
     * @param  string  $permKey  e.g. "admin:orders:read"
     */
    public function handle(Request $request, Closure $next, string $permKey): Response
    {
        $user = $request->user();
        if (! $user || $user->role !== 'admin' || $user->is_banned) {
            return $this->deny($request, '需要管理员权限');
        }

        $permKey = trim($permKey);
        if ($permKey === '') {
            return $next($request);
        }

        $keys = method_exists($user, 'adminPermissions') ? (array) $user->adminPermissions() : [];
        $keys = array_values(array_filter(array_map('strval', $keys)));

        if (in_array('*', $keys, true) || in_array($permKey, $keys, true)) {
            return $next($request);
        }

        // 支持模块通配：admin:orders:* / admin:orders
        $parts = explode(':', $permKey);
        $module = $parts[1] ?? null;
        if ($module) {
            if (in_array("admin:{$module}:*", $keys, true) || in_array("admin:{$module}", $keys, true)) {
                return $next($request);
            }
        }

        return $this->deny($request, '缺少菜单权限：'.$permKey);
    }

    private function deny(Request $request, string $message): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => $message], Response::HTTP_FORBIDDEN);
        }

        throw new HttpException(Response::HTTP_FORBIDDEN, $message);
    }
}

