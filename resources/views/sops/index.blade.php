@extends('layouts.site')

@section('title', '私域运营 SOP — ' . ($ocSite['site_name'] ?? 'OpenClaw 智信'))

@section('content')
    <div class="mb-8">
        <h1 class="text-3xl font-bold oc-heading mb-2">私域运营 SOP</h1>
        <p class="text-sm oc-muted">平台、清单与模板；标有 VIP 的条目需会员阅读全文。</p>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($sops as $sop)
            <a href="{{ route('sops.show', $sop) }}" class="oc-surface p-5 block rounded-xl oc-quick-tile" style="text-decoration: none;">
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
            </a>
        @empty
            <div class="oc-surface p-8 text-center text-sm oc-muted sm:col-span-2 lg:col-span-3">暂无 SOP，请稍后再来或由管理员在后台添加。</div>
        @endforelse
    </div>

    @if ($sops->count() > 0)
        <div class="mt-10">{{ $sops->links() }}</div>
    @endif
@endsection
