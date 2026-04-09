<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        <meta name="user-id" content="{{ auth()->id() }}">
        <meta name="user-role" content="{{ auth()->user()->role }}">
    @endauth
    <title>{{ $ocSite['site_name'] ?? 'OpenClaw 智信' }}</title>
    <script>
        try {
            var __sk = localStorage.getItem('preferred_skin');
            if (__sk) document.documentElement.setAttribute('data-skin', __sk);
        } catch (e) {}
    </script>
    
    <script>
        tailwind.config = { corePlugins: { preflight: false } };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/skins.css') }}">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <meta name="theme-color" content="#0d9488">
    
    <style>
        /* Hero Section 渐变背景 */
        .hero-gradient {
            background: var(--gradient-primary, linear-gradient(135deg, #667eea 0%, #764ba2 100%));
        }
        
        /* 渐变文字 */
        .gradient-text {
            background: var(--gradient-primary, linear-gradient(135deg, #667eea 0%, #764ba2 100%));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* 卡片悬停效果 */
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        /* 平滑滚动 */
        html {
            scroll-behavior: smooth;
        }
        
        /* 实时动态滚动动画 */
        @keyframes scroll-up {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-100%); }
        }
        
        .scroll-animation {
            animation: scroll-up 30s linear infinite;
        }
        
        .scroll-animation:hover {
            animation-play-state: paused;
        }

        @keyframes testimonial-track-rtl {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .oc-testimonial-slider {
            overflow: hidden;
            position: relative;
        }
        .oc-testimonial-track {
            display: flex;
            width: max-content;
            gap: 1.25rem;
            animation: testimonial-track-rtl 28s linear infinite;
            will-change: transform;
        }
        .oc-testimonial-track:hover {
            animation-play-state: paused;
        }
        .oc-testimonial-card {
            width: min(360px, calc(100vw - 2.5rem));
            flex: 0 0 auto;
        }
        .oc-feature-card {
            width: min(340px, calc(100vw - 2.5rem));
            flex: 0 0 auto;
        }
    </style>
</head>
<body class="bg-gray-50 pt-16">
    <!-- 顶部导航栏 -->
    @include('partials.navbar')
    @include('partials.announcement-marquee', ['placement' => 'top'])
    @include('partials.flash')
    {{-- 通栏横幅：仅当 home-banner 后台「展示位置」为顶部（或留空）时渲染；左右侧改由下方侧栏展示 --}}
    @include('partials.ad-slot', ['code' => 'home-banner', 'bannerPlacement' => 'top'])

    <div class="max-w-7xl mx-auto px-4 w-full">
        <div class="w-full min-w-0">
    <section class="hero-gradient text-white py-32 min-h-[600px] flex flex-col justify-center">
        <div class="max-w-7xl mx-auto px-4 text-center w-full">
            <h1 class="text-5xl md:text-6xl font-bold mb-6 flex flex-wrap items-center justify-center gap-4">
                <img src="{{ $ocSite['site_logo_href'] ?? asset('favicon.svg') }}" alt="" width="56" height="56" class="shrink-0 rounded-lg shadow-md" decoding="async" />
                <span>{{ $ocSite['site_name'] ?? 'OpenClaw 智信' }}</span>
            </h1>
            <p class="text-2xl mb-12 opacity-90">
                {{ $ocSite['site_slogan'] ?? '' }}
            </p>
            <div class="flex justify-center gap-6 flex-wrap">
                @guest
                    <a href="{{ route('register', ['trial' => 7]) }}" class="oc-hero-cta-solid">
                        免费试用 7 天
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="oc-hero-cta-solid">
                        进入个人中心
                    </a>
                    @if (! auth()->user()->hasMemberMenuPrivileges())
                        <a href="{{ route('pricing') }}" class="oc-hero-cta-outline inline-block text-center">
                            升级会员
                        </a>
                    @endif
                @endguest
                <button type="button" onclick="document.getElementById('vip-preview').scrollIntoView({ behavior: 'smooth' })"
                        class="oc-hero-cta-outline">
                    看看有什么
                </button>
            </div>
        </div>
    </section>

    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-10">🔥 精品内容</h2>
            <div class="space-y-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">精品文章</h3>
                    <div class="oc-testimonial-slider"><div class="oc-testimonial-track">
                        @foreach ([$featuredArticles, $featuredArticles] as $grp)
                            @foreach ($grp as $a)
                                <a href="{{ route('articles.show', $a) }}" class="oc-feature-card bg-gray-50 rounded-xl p-5 block" style="text-decoration:none;color:inherit;">
                                    <div class="font-semibold line-clamp-2">{{ $a->title }}</div>
                                    <div class="text-xs text-gray-500 mt-2">👍 {{ number_format((int) $a->like_count) }} · 👁 {{ number_format((int) $a->view_count) }}</div>
                                </a>
                            @endforeach
                        @endforeach
                    </div></div>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">精品项目</h3>
                    <div class="oc-testimonial-slider"><div class="oc-testimonial-track">
                        @foreach ([$featuredProjects, $featuredProjects] as $grp)
                            @foreach ($grp as $p)
                                <a href="{{ route('projects.show', $p) }}" class="oc-feature-card bg-gray-50 rounded-xl p-5 block" style="text-decoration:none;color:inherit;">
                                    <div class="font-semibold line-clamp-2">{{ $p->name }}</div>
                                    <div class="text-xs text-gray-500 mt-2">⭐ {{ number_format((int) $p->stars) }} · ⑂ {{ number_format((int) $p->forks) }}</div>
                                </a>
                            @endforeach
                        @endforeach
                    </div></div>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">精品案例</h3>
                    <div class="oc-testimonial-slider"><div class="oc-testimonial-track">
                        @foreach ([$featuredCases, $featuredCases] as $grp)
                            @foreach ($grp as $c)
                                <a href="{{ route('cases.show', $c) }}" class="oc-feature-card bg-gray-50 rounded-xl p-5 block" style="text-decoration:none;color:inherit;">
                                    <div class="font-semibold line-clamp-2">{{ $c->title }}</div>
                                    <div class="text-xs text-gray-500 mt-2">👍 {{ number_format((int) $c->like_count) }} · 👁 {{ number_format((int) $c->view_count) }}</div>
                                </a>
                            @endforeach
                        @endforeach
                    </div></div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 数据展示区 -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-5xl font-bold gradient-text mb-2">{{ number_format((int) ($homeStats['users'] ?? 0)) }}+</div>
                    <div class="text-gray-600 text-lg">注册用户</div>
                </div>
                <div>
                    <div class="text-5xl font-bold gradient-text mb-2">{{ number_format((int) ($homeStats['cases'] ?? 0)) }}+</div>
                    <div class="text-gray-600 text-lg">副业案例</div>
                </div>
                <div>
                    <div class="text-5xl font-bold gradient-text mb-2">{{ number_format((int) ($homeStats['tools'] ?? 0)) }}+</div>
                    <div class="text-gray-600 text-lg">工具地图</div>
                </div>
                <div>
                    <div class="text-5xl font-bold gradient-text mb-2">{{ $homeStats['revenue_text'] ?? '200 万+' }}</div>
                    <div class="text-gray-600 text-lg">累计变现</div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- 实时动态区 -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-12">
                📢 实时动态
            </h2>
            <div class="bg-white rounded-2xl shadow-lg p-8 overflow-hidden relative h-64">
                <div class="scroll-animation">
                    @forelse($vipActivities as $activity)
                    <div class="flex items-center justify-between py-4 border-b border-gray-100">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white font-bold">
                                {{ mb_substr($activity->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="font-semibold text-gray-800">{{ $activity->name }}</div>
                                <div class="text-sm text-gray-500">开通了{{ $activity->plan_text }}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold gradient-text">¥{{ number_format($activity->amount) }}</div>
                            <div class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-gray-500 py-20">
                        暂无动态数据
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
    
    <!-- VIP 内容预览区 -->
    <section id="vip-preview" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-4">
                🔒 VIP 专属内容
            </h2>
            <p class="text-center text-gray-600 mb-12 text-lg">
                开通 VIP，解锁全部高质量内容
            </p>
            <div class="grid md:grid-cols-3 gap-8 items-stretch">
                @foreach ($vipPreviews as $preview)
                    @if (! empty($preview['locked']))
                        <div class="bg-gray-50 rounded-2xl p-8 card-hover relative ring-1 ring-gray-200 flex flex-col h-full min-h-[280px] overflow-hidden">
                            <div class="text-5xl mb-4 shrink-0">{{ $preview['icon'] }}</div>
                            <h3 class="text-2xl font-bold mb-3 text-gray-800 shrink-0 line-clamp-2">{{ $preview['title'] }}</h3>
                            <p class="text-gray-600 mb-0 flex-1 text-sm leading-relaxed line-clamp-4">{{ $preview['summary'] }}</p>
                            <span class="oc-cta-primary mt-6 shrink-0">开通 VIP 查看</span>
                            @include('partials.access-mask', [
                                'title' => 'VIP 专属内容',
                                'desc' => '开通会员后可查看完整案例、工具与 SOP 内容。',
                                'cta' => '立即开通 VIP',
                            ])
                        </div>
                    @else
                        <a href="{{ $preview['url'] }}" class="flex flex-col h-full min-h-[280px] bg-gray-50 rounded-2xl p-8 card-hover relative oc-home-vip-card" style="text-decoration: none; color: inherit;">
                            <div class="text-5xl mb-4 shrink-0">{{ $preview['icon'] }}</div>
                            <h3 class="text-2xl font-bold mb-3 text-gray-800 shrink-0 line-clamp-2">{{ $preview['title'] }}</h3>
                            <p class="text-gray-600 mb-0 flex-1 text-sm leading-relaxed line-clamp-4">{{ $preview['summary'] }}</p>
                            <span class="oc-cta-primary mt-6 shrink-0">
                                查看详情
                            </span>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
    
    <!-- 用户评价区 -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-12">
                💬 用户评价
            </h2>
            <div class="oc-testimonial-slider">
                @if ($testimonials->count() > 0)
                <div class="oc-testimonial-track">
                    @foreach ([$testimonials, $testimonials] as $testimonialGroup)
                    @foreach ($testimonialGroup as $t)
                    <div class="oc-testimonial-card bg-white rounded-2xl p-8 card-hover flex flex-col min-h-[260px]">
                        <div class="flex items-center gap-4 mb-4 shrink-0">
                            <div class="w-16 h-16 rounded-full bg-gradient-to-br {{ $t->gradient_from }} {{ $t->gradient_to }} flex items-center justify-center text-white text-2xl font-bold shrink-0">
                                {{ mb_substr($t->avatar_initial, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-lg truncate">{{ $t->display_name }}</div>
                                @if ($t->caption)
                                    <div class="text-sm text-gray-500 truncate">{{ $t->caption }}</div>
                                @endif
                            </div>
                        </div>
                        <p class="text-gray-700 leading-relaxed flex-1 text-sm line-clamp-5 m-0">
                            「{{ $t->body }}」
                        </p>
                        <div class="flex mt-4 text-yellow-500 shrink-0 text-sm" aria-label="评分 {{ $t->rating }} 星">
                            @for ($i = 0; $i < min(5, max(1, (int) $t->rating)); $i++)
                                ⭐
                            @endfor
                        </div>
                    </div>
                    @endforeach
                    @endforeach
                </div>
                @else
                <p class="text-center text-gray-500 m-0">暂无评价展示。</p>
                @endif
            </div>
        </div>
    </section>
    
    <!-- 价格方案区 -->
    <section class="py-12 md:py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-4">
                💎 价格方案
            </h2>
            <p class="text-center text-gray-600 mb-8 text-lg">
                选择适合你的方案，立即开启变现之旅
            </p>
            <div class="grid md:grid-cols-3 gap-8 items-stretch">
                @foreach($pricingPlans as $plan)
                <div class="bg-gray-50 rounded-2xl p-8 card-hover relative flex flex-col h-full min-h-[420px] {{ $plan['highlight'] ? 'oc-pricing-card--featured' : '' }}">
                    @if($plan['highlight'])
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 oc-pricing-hot-badge px-6 py-2 rounded-full text-sm font-bold">
                        🔥 热门推荐
                    </div>
                    @endif
                    
                    <h3 class="text-3xl font-bold mb-4 text-gray-800 shrink-0">{{ $plan['name'] }}</h3>
                    @if (! empty($plan['promo_label']))
                        <p class="text-sm font-semibold text-orange-600 mb-2 m-0 shrink-0">{{ $plan['promo_label'] }}</p>
                    @endif
                    <div class="mb-4 shrink-0">
                        @if (! empty($plan['original_price']))
                            <div class="text-sm text-gray-400 line-through mb-1">原价 ¥{{ $plan['original_price'] }}/{{ $plan['period'] }}</div>
                        @endif
                        <span class="text-5xl font-bold gradient-text">¥{{ $plan['price'] }}</span>
                        <span class="text-gray-500">/{{ $plan['period'] }}</span>
                    </div>
                    @include('partials.pricing-marketing', ['plan' => $plan])
                    
                    <ul class="space-y-3 mb-0 flex-1 text-sm">
                        @foreach($plan['features'] as $feature)
                        <li class="text-gray-700">{{ $feature }}</li>
                        @endforeach
                    </ul>
                    
                    <div class="mt-8 shrink-0">
                    @if($plan['price'] > 0)
                    <a href="{{ route('payments.confirm', ['plan' => $plan['plan_key'] ?? 'vip']) }}" class="oc-cta-primary">
                        立即开通
                    </a>
                    @else
                    <a href="{{ route('register') }}" class="oc-cta-outline">
                        免费注册
                    </a>
                    @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

        </div>
    </div>

    {{-- 通栏底部：仅当 home-banner 展示位置为「底部」时由 partial 内逻辑决定是否输出 --}}
    @include('partials.ad-slot', ['code' => 'home-banner', 'bannerPlacement' => 'bottom'])

    @include('partials.announcement-marquee', ['placement' => 'bottom'])
    @include('partials.announcement-float')
    @include('partials.floating-ads')

    <!-- 底部页脚 -->
    @include('partials.footer')
    
    <!-- Toast 容器 -->
    <div id="toast-container"></div>
    
    @vite(['resources/js/blade-skin-mount.js'])
    @include('partials.site-user-mini')
    <script>
        document.querySelectorAll('[data-oc-flash]').forEach(function (el) {
            setTimeout(function () {
                el.style.opacity = '0';
                setTimeout(function () {
                    el.remove();
                }, 280);
            }, 3000);
        });
    </script>
    @include('partials.pricing-countdown-init')
    <!-- 脚本 -->
    @stack('scripts')
</body>
</html>
