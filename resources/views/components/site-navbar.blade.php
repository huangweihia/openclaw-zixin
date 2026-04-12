{{--
  站点顶栏（对齐原型：左品牌 | 中导航 | 右操作）
  换肤：Vue SkinSwitcher 挂载在 #blade-skin-switcher（在搜索与通知与用户菜单之间）
--}}
@php
    $ocSite = $ocSite ?? \App\Support\SiteViewComposer::branding();
    $unreadNotifications = 0;
    if (auth()->check()) {
        $unreadNotifications = auth()->user()->inboxNotifications()->where('is_read', false)->count();
    }
    $user = auth()->user();
    $vipDaysLeft = null;
    if ($user && $user->isVip() && $user->subscription_ends_at && $user->subscription_ends_at->isFuture()) {
        $vipDaysLeft = (int) now()->startOfDay()->diffInDays($user->subscription_ends_at->copy()->startOfDay());
    }
    $isAdminUser = $user && $user->isAdmin();
    $vipTitle = match ($user?->role) {
        'svip' => 'SVIP 会员',
        'vip' => 'VIP 会员',
        'admin' => '管理员',
        default => 'VIP 会员',
    };
@endphp
<nav class="oc-site-nav" aria-label="主导航">
    <div class="oc-site-nav__inner">
        <div class="oc-site-nav__brand">
            <a href="{{ route('home') }}" class="oc-site-nav__logo">
                <img src="{{ $ocSite['site_logo_href'] ?? asset('favicon.svg') }}" alt="" class="oc-site-nav__logo-mark" width="28" height="28" decoding="async" />
                <span class="oc-site-nav__logo-text">
                    <span class="oc-site-nav__logo-ai">AI</span>
                    <span class="oc-site-nav__logo-name">{{ $ocSite['site_name'] }}</span>
                </span>
            </a>
        </div>

        <div class="oc-site-nav__menu">
            <a href="{{ route('home') }}" class="oc-site-nav__link">首页</a>
            <a href="{{ route('articles.index') }}" class="oc-site-nav__link">文章</a>
            <a href="{{ route('projects.index') }}" class="oc-site-nav__link">项目</a>
            <a href="{{ route('cases.index') }}" class="oc-site-nav__link">案例</a>
            <a href="{{ route('tools.index') }}" class="oc-site-nav__link">工具</a>
            <a href="{{ route('posts.index') }}" class="oc-site-nav__link">投稿</a>
            <a href="{{ route('sops.index') }}" class="oc-site-nav__link">SOP</a>
            <a href="{{ route('vip') }}" class="oc-site-nav__link">VIP</a>
        </div>

        <div class="oc-site-nav__actions">
            <a href="{{ route('search') }}" class="oc-site-nav__icon-btn" title="搜索" aria-label="搜索">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </a>

            @auth
                <a href="{{ route('notifications.index') }}" class="oc-site-nav__icon-btn {{ $unreadNotifications > 0 ? 'oc-site-nav__icon-btn--dot' : '' }}"
                    title="通知" aria-label="通知">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @if ($unreadNotifications > 0)
                        <span class="oc-site-nav__badge" aria-hidden="true">{{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}</span>
                    @endif
                </a>
            @endauth

            {{-- 换肤：与 SkinSwitcher.vue（navbar 变体）对接，勿删此节点 --}}
            <div id="blade-skin-switcher" class="oc-site-nav__skin-slot"></div>

            @auth
                <details class="oc-user-dropdown" id="oc-user-dropdown">
                    <summary class="oc-user-dropdown__trigger" aria-haspopup="menu" aria-expanded="false">
                        <span class="oc-user-dropdown__avatar-wrap">
                            @if ($user->avatar)
                                <img src="{{ $user->avatar }}" alt="" class="oc-user-dropdown__avatar-img" width="40" height="40" />
                            @else
                                <span class="oc-user-dropdown__avatar-fallback">{{ mb_substr($user->name, 0, 1) }}</span>
                            @endif
                        </span>
                        <svg class="oc-user-dropdown__chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="oc-user-dropdown__panel" role="menu">
                        <a href="{{ route('dashboard') }}" class="oc-user-dropdown__item" role="menuitem">个人中心</a>
                        <a href="{{ route('favorites.index') }}" class="oc-user-dropdown__item" role="menuitem">我的收藏</a>
                        <a href="{{ route('user-posts.index') }}" class="oc-user-dropdown__item" role="menuitem">我的发布</a>
                        <a href="{{ route('dashboard', ['tab' => 'orders']) }}" class="oc-user-dropdown__item" role="menuitem">我的订单</a>
                        <hr class="oc-user-dropdown__sep" />
                        @if ($user->hasMemberMenuPrivileges())
                            <div class="oc-user-dropdown__vip" role="presentation">
                                <span class="oc-user-dropdown__vip-title">👑 {{ $vipTitle }}</span>
                                @if ($isAdminUser)
                                    <span class="oc-user-dropdown__vip-sub">全站权益 · 无需开通会员</span>
                                @elseif ($vipDaysLeft !== null)
                                    <span class="oc-user-dropdown__vip-sub">剩余 {{ $vipDaysLeft }} 天</span>
                                @else
                                    <span class="oc-user-dropdown__vip-sub">会员服务进行中</span>
                                @endif
                            </div>
                        @else
                            <a href="{{ route('pricing') }}" class="oc-user-dropdown__item oc-user-dropdown__item--accent" role="menuitem">👑 开通 VIP</a>
                        @endif
                        <hr class="oc-user-dropdown__sep" />
                        <form method="post" action="{{ route('logout') }}" class="oc-user-dropdown__form">
                            @csrf
                            <button type="submit" class="oc-user-dropdown__item oc-user-dropdown__item--danger" role="menuitem">退出登录</button>
                        </form>
                    </div>
                </details>
            @else
                <a href="{{ route('login') }}" class="oc-site-nav__auth-link">登录</a>
                <a href="{{ route('register') }}" class="oc-site-nav__btn-register">免费注册</a>
            @endauth
        </div>
    </div>
</nav>

<script>
    (function () {
        var d = document.getElementById('oc-user-dropdown');
        if (!d) return;
        var sum = d.querySelector('summary');
        d.addEventListener('toggle', function () {
            if (sum) sum.setAttribute('aria-expanded', d.open ? 'true' : 'false');
            if (!d.open) return;
            setTimeout(function () {
                function close(ev) {
                    if (!d.contains(ev.target)) {
                        d.open = false;
                        document.removeEventListener('click', close, true);
                    }
                }
                document.addEventListener('click', close, true);
            }, 0);
        });
    })();
</script>
