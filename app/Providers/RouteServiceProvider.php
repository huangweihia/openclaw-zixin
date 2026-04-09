<?php

namespace App\Providers;

use App\Http\Controllers\Admin\AdminSpaController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/';

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // 管理端 SPA 登录依赖 Session（/sanctum/csrf-cookie + Cookie 会话）。
            // 若仅用 api 中间件会缺少 session store，导致「Session store not set on request」。
            Route::middleware([
                'web',
                \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            ])
                ->prefix('api/admin')
                ->group(base_path('routes/admin_api.php'));

            $adminDomain = config('admin.domain');
            $frontDomain = config('app.front_domain');
            $useSplitHosts = is_string($adminDomain) && $adminDomain !== ''
                && is_string($frontDomain) && $frontDomain !== '';

            if ($useSplitHosts) {
                Route::domain($frontDomain)->middleware('web')->group(base_path('routes/web.php'));

                Route::domain($frontDomain)->middleware('web')->group(function () use ($adminDomain) {
                    Route::get('/admin/{path?}', function (Request $request, ?string $path = null) use ($adminDomain) {
                        $target = $request->getScheme().'://'.$adminDomain;
                        $port = $request->getPort();
                        if (! in_array($port, [80, 443], true)) {
                            $target .= ':'.$port;
                        }
                        $suffix = $path !== null && $path !== '' ? '/'.ltrim($path, '/') : '';
                        $qs = $request->getQueryString();
                        if (is_string($qs) && $qs !== '') {
                            $suffix .= '?'.$qs;
                        }

                        return redirect()->away($target.$suffix);
                    })->where('path', '.*');
                });

                Route::domain($adminDomain)->middleware('web')->group(function () {
                    Route::get('/{any?}', [AdminSpaController::class, 'index'])
                        ->where('any', '.*')
                        ->name('admin.spa');
                });
            } else {
                if (is_string($adminDomain) && $adminDomain !== '') {
                    Log::warning('已配置 ADMIN_DOMAIN 但未设置 APP_FRONT_DOMAIN，已回退为路径前缀后台（/admin/*）。');
                }

                Route::middleware('web')->group(base_path('routes/web.php'));

                Route::middleware('web')
                    ->prefix(trim(config('admin.path_prefix', 'admin'), '/'))
                    ->group(function () {
                        Route::get('/{any?}', [AdminSpaController::class, 'index'])
                            ->where('any', '.*')
                            ->name('admin.spa');
                    });
            }
        });
    }
}
