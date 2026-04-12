{{-- 归档：首页曾展示的「用户评价」区块（自 2026-04 起首页不再引用，保留备查；勿删本说明） --}}
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
