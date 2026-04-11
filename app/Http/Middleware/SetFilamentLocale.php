<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Filament v3 无 Panel::defaultLocale()；在面板中间件栈里设置应用语言。
 */
class SetFilamentLocale
{
    public function handle(Request $request, Closure $next): mixed
    {
        app()->setLocale('zh_CN');

        return $next($request);
    }
}
