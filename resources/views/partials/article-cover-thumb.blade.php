{{--
  文章封面：底层渐变 + 矢量图标；有 URL 时叠真实图片，加载失败则移除 img，永不显示浏览器裂图。
  $cover：封面 URL；$variant：card | hero | modal
--}}
@php
    $raw = $cover ?? null;
    $url = is_string($raw) ? trim($raw) : '';
    $hasUrl = $url !== '';
    $variant = $variant ?? 'card';
    $wrapperClass = match ($variant) {
        'hero' => 'relative w-full overflow-hidden rounded-xl',
        'modal' => 'relative w-full overflow-hidden rounded-lg',
        default => 'oc-card-thumb relative overflow-hidden',
    };
    $boxStyle = match ($variant) {
        'hero' => 'min-height:14rem;height:16rem;max-height:28rem;',
        'modal' => 'min-height:220px;max-height:85vh;',
        default => '',
    };
    $imgClass = match ($variant) {
        'hero' => 'absolute inset-0 z-[1] h-full w-full object-cover',
        'modal' => 'absolute inset-0 z-[1] h-full w-full max-h-[85vh] object-contain',
        default => 'absolute inset-0 z-[1] h-full w-full object-cover',
    };
@endphp
<div
    class="{{ $wrapperClass }}"
    style="background: linear-gradient(160deg, #e8eef5 0%, #cfd8e6 100%); {{ $boxStyle }}"
>
    <div class="absolute inset-0 flex items-center justify-center" style="z-index: 0; pointer-events: none;" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.15" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="5" width="18" height="14" rx="2" ry="2"/>
            <circle cx="8.5" cy="10" r="1.5"/>
            <path d="M21 17l-5-5-4 4-2-2-5 5"/>
        </svg>
    </div>
    @if ($hasUrl)
        <img
            src="{{ $url }}"
            alt=""
            loading="{{ $variant === 'modal' ? 'eager' : 'lazy' }}"
            decoding="async"
            class="{{ $imgClass }}"
            style="z-index: 1;"
            onerror="this.remove()"
        />
    @endif
</div>
