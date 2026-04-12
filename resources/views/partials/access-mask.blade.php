@php
    $ocMaskTitle = $title ?? '该内容需要会员权限';
    $ocMaskDesc = $desc ?? '开通会员后可查看完整内容。';
    $ocMaskCta = $cta ?? '去开通会员';
    $ocMaskHref = $href ?? route('pricing');
    $ocMaskLevel = strtolower((string) ($level ?? 'vip'));
    $ocMaskEmoji = $ocMaskLevel === 'svip' ? '👑' : '🔒';
@endphp
{{-- 整块遮罩：全幅毛玻璃 + 网格纹理，点击任意处跳转开通页（无穿透到底层链接） --}}
<a
    href="{{ $ocMaskHref }}"
    class="oc-permission-mask-link {{ $ocMaskLevel === 'svip' ? 'oc-permission-mask-link--svip' : 'oc-permission-mask-link--vip' }}"
>
    <span class="oc-permission-mask-link__glass" aria-hidden="true"></span>
    <span class="oc-permission-mask-link__mosaic" aria-hidden="true"></span>
    <span class="oc-permission-mask-link__inner">
        <span class="oc-permission-mask-link__emoji" aria-hidden="true">{{ $ocMaskEmoji }}</span>
        <span class="oc-permission-mask-link__title">{{ $ocMaskTitle }}</span>
        <span class="oc-permission-mask-link__desc">{{ $ocMaskDesc }}</span>
        <span class="oc-permission-mask-link__cta">{{ $ocMaskCta }}</span>
    </span>
</a>
