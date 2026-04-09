<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || $user->role !== 'admin' || $user->is_banned) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => '需要管理员权限'], Response::HTTP_FORBIDDEN);
            }
            abort(Response::HTTP_FORBIDDEN, '需要管理员权限');
        }

        return $next($request);
    }
}
