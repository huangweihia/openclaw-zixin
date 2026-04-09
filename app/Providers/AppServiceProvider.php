<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\InboxNotification;
use App\Services\AdPresentationService;
use App\Support\SiteViewComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 站点前台分页：统一使用项目自定义样式，避免默认 Tailwind/Bootstrap 标记不匹配导致“很丑”
        Paginator::defaultView('partials.pagination');
        Paginator::defaultSimpleView('partials.pagination-simple');

        View::composer(
            [
                'partials.footer',
                'components.site-navbar',
                'layouts.site',
                'home',
            ],
            SiteViewComposer::class
        );

        View::composer('components.top-nav', function ($view) {
            $unreadInboxCount = 0;
            if (auth()->check()) {
                $unreadInboxCount = InboxNotification::query()
                    ->where('user_id', auth()->id())
                    ->where('is_read', false)
                    ->count();
            }
            $view->with('unreadInboxCount', $unreadInboxCount);
        });

        View::composer('partials.announcement-marquee', function ($view) {
            $placement = (string) ($view->getData()['placement'] ?? 'top');
            if (! in_array($placement, ['top', 'bottom'], true)) {
                $placement = 'top';
            }
            $q = Announcement::query()
                ->where('is_published', true)
                ->where(function ($query) {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                });

            if (Schema::hasColumn('announcements', 'display_position')) {
                $q->where(function ($sub) use ($placement) {
                    $sub->where('display_position', $placement);
                    if ($placement === 'top') {
                        $sub->orWhereNull('display_position')->orWhere('display_position', '');
                    }
                });
            } elseif ($placement === 'bottom') {
                $q->whereRaw('1 = 0');
            }

            if (Schema::hasColumn('announcements', 'is_floating')) {
                $q->where(function ($sub) {
                    $sub->where('is_floating', false)->orWhereNull('is_floating');
                });
            }

            $rows = $q
                ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
                ->orderByDesc('published_at')
                ->limit(20)
                ->get();
            $view->with('marqueeAnnouncements', $rows);
        });

        View::composer('partials.announcement-float', function ($view) {
            if (! Schema::hasColumn('announcements', 'is_floating')) {
                $view->with('floatingAnnouncements', collect());

                return;
            }
            $rows = Announcement::query()
                ->where('is_published', true)
                ->where('is_floating', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
                ->orderByDesc('published_at')
                ->limit(1)
                ->get();
            $view->with('floatingAnnouncements', $rows);
        });

        View::composer('partials.floating-ads', function ($view) {
            $view->with('floatingAdPacks', app(AdPresentationService::class)->resolveFloatSlotPacks());
        });

        View::composer('partials.ad-rail', function ($view) {
            $position = (string) ($view->getData()['railPosition'] ?? 'left');
            if (! in_array($position, ['left', 'right'], true)) {
                $position = 'left';
            }
            $pack = app(AdPresentationService::class)->resolveFirstBySlotPosition($position);
            $view->with('railAdPack', $pack);
        });
    }
}
