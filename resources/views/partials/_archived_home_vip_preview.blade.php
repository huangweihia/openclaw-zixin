{{-- 归档：首页曾展示的「VIP 专属内容」区块（自 2026-04 起首页不再引用，保留备查；勿删本说明） --}}
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
                    <div class="bg-white rounded-2xl p-8 card-hover relative ring-1 ring-slate-200/90 shadow-sm flex flex-col h-full min-h-[260px] overflow-hidden">
                        <div class="text-5xl mb-4 shrink-0">{{ $preview['icon'] }}</div>
                        <h3 class="text-2xl font-bold mb-3 text-gray-800 shrink-0 line-clamp-2">{{ $preview['title'] }}</h3>
                        <p class="text-gray-600 mb-0 flex-1 text-sm leading-relaxed line-clamp-4">{{ $preview['summary'] }}</p>
                        @include('partials.vip-mask', [
                            'title' => 'VIP 专属内容',
                            'desc' => '开通会员后可查看完整案例、工具与 SOP 内容。',
                            'cta' => auth()->check() ? '立即开通 VIP' : '登录 / 开通 VIP',
                            'href' => auth()->check() ? route('pricing') : route('login', ['return' => url()->current()]),
                        ])
                    </div>
                @else
                    <a href="{{ $preview['url'] }}" class="flex flex-col h-full min-h-[260px] bg-white rounded-2xl p-8 card-hover relative ring-1 ring-slate-200/90 shadow-sm oc-home-vip-card" style="text-decoration: none; color: inherit;">
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
