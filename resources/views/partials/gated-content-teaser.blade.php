{{-- 会员门槛：模糊摘要 + 全幅 access-mask；mask 为 SiteGateMask 返回的数组 --}}
@props([
    'teaserHtml' => '',
    'mask' => [],
])
<div class="relative rounded-xl overflow-hidden border border-slate-200/80 bg-slate-50/40 min-h-[12rem]">
    <div class="mb-0 p-6 md:p-8 max-h-[14rem] overflow-hidden relative">
        <div class="pointer-events-none select-none opacity-75 blur-[2px] article-content text-sm max-w-none">
            {!! $teaserHtml !!}
        </div>
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-slate-100/95 to-transparent"></div>
    </div>
    @include('partials.access-mask', [
        'level' => $mask['level'] ?? 'vip',
        'title' => $mask['title'] ?? '会员专属内容',
        'desc' => $mask['desc'] ?? '开通会员后可查看全文。',
        'cta' => $mask['cta'] ?? '去开通',
        'href' => $mask['href'] ?? route('pricing'),
    ])
</div>
