@php
    $ocMaskTitle = $title ?? '该内容需要会员权限';
    $ocMaskDesc = $desc ?? '开通 VIP / SVIP 后可查看完整内容。';
    $ocMaskCta = $cta ?? '去开通会员';
    $ocMaskHref = $href ?? route('pricing');
    $ocMaskLevel = strtolower((string) ($level ?? 'vip'));
@endphp

<div class="oc-access-mask {{ $ocMaskLevel === 'svip' ? 'oc-access-mask--svip' : 'oc-access-mask--vip' }}">
    <div class="oc-access-mask__backdrop"></div>
    <div class="oc-access-mask__panel">
        <div class="text-3xl mb-2" aria-hidden="true">{{ $ocMaskLevel === 'svip' ? '👑' : '🔒' }}</div>
        <p class="font-semibold mb-2 oc-heading">{{ $ocMaskTitle }}</p>
        <p class="text-sm oc-muted mb-4">{{ $ocMaskDesc }}</p>
        <a href="{{ $ocMaskHref }}" class="btn btn-primary">{{ $ocMaskCta }}</a>
    </div>
</div>
