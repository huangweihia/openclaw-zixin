@extends('layouts.site')

@section('title', '私域运营 SOP — ' . ($ocSite['site_name'] ?? 'OpenClaw 智信'))

@section('content')
    <div class="mb-8">
        <h1 class="text-3xl font-bold oc-heading mb-2">私域运营 SOP</h1>
        <p class="text-sm oc-muted">平台、清单与模板；标有 VIP 的条目需会员阅读全文。</p>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($sops as $sop)
            @php
                $sopCardLocked = ! $sop->userCanReadFull(auth()->user());
            @endphp
            <div class="oc-surface p-5 rounded-xl oc-quick-tile relative overflow-hidden">
                @if ($sopCardLocked)
                    <div class="block" style="text-decoration: none; color: inherit;">
                @else
                    <a href="{{ route('sops.show', $sop) }}" class="block" style="text-decoration: none; color: inherit;">
                @endif
                    <div class="flex justify-between gap-2 mb-2">
                        <span class="text-xs oc-muted">{{ $sop->platform }} · {{ $sop->type }}</span>
                        @if ($sop->visibility === 'vip')
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded" style="background: color-mix(in srgb, var(--primary) 20%, transparent); color: var(--primary);">VIP</span>
                        @endif
                    </div>
                    <h2 class="text-base font-bold oc-heading m-0">{{ $sop->title }}</h2>
                    @if ($sop->summary)
                        <p class="text-xs oc-muted mt-2 mb-0 line-clamp-2">{{ $sop->summary }}</p>
                    @endif
                @if ($sopCardLocked)
                    </div>
                    <x-vip-content-lock title="VIP 专属 SOP" desc="开通 VIP 后可查看完整清单与模板。" cta="开通 VIP 阅读全文" :href="route('pricing')" />
                @else
                    </a>
                @endif
            </div>
        @empty
            <div class="oc-surface p-8 text-center text-sm oc-muted sm:col-span-2 lg:col-span-3">暂无 SOP，请稍后再来或由管理员在后台添加。</div>
        @endforelse
    </div>

    @if ($sops->count() > 0)
        <div class="mt-10">{{ $sops->links() }}</div>
    @endif
@endsection
