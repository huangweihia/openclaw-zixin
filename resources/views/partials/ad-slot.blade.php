@php
    $slotCode = $code ?? 'home-banner';
    /** @var 'top'|'bottom' $bannerPlacement 仅 home-banner 通栏用：与后台「展示位置」联动 */
    $bannerPlacement = $bannerPlacement ?? 'top';
    $adPack = app(\App\Services\AdPresentationService::class)->resolve($slotCode);

    if ($adPack && $slotCode === 'home-banner') {
        $pos = (string) ($adPack['slot']->position ?? 'top');
        // 左侧 / 右侧：只出现在 layouts / 首页侧栏 partial 中，不在顶部通栏占位
        if (in_array($pos, ['left', 'right'], true)) {
            $adPack = null;
        } elseif ($pos === 'bottom' && $bannerPlacement !== 'bottom') {
            $adPack = null;
        } elseif (in_array($pos, ['top', ''], true) && $bannerPlacement !== 'top') {
            $adPack = null;
        }
    }
@endphp
@if ($adPack)
    @php
        $slot = $adPack['slot'];
        $w = (int) ($slot->width ?? 0);
        $h = (int) ($slot->height ?? 0);
        $isHomeBanner = $slotCode === 'home-banner';
        $boxStyle = '';
        if ($w > 0) {
            $boxStyle .= 'width: min(100%, '.$w.'px); max-width: min(100%, '.$w.'px);';
        }
        if ($h > 0) {
            // 首页横幅：不强制固定高度，避免移动端出现大块空白；只限制最大高度并受视口限制
            if ($isHomeBanner) {
                $boxStyle .= ' max-height: min('.$h.'px, 40vh);';
            } else {
                $boxStyle .= ' height: '.$h.'px; max-height: '.$h.'px;';
            }
        }
        if ($w > 0 || $h > 0) {
            $boxStyle .= ' overflow: hidden;';
        }
        $wrapClass = trim(
            'oc-ad-slot oc-surface mb-6 overflow-hidden rounded-xl relative '
            . (($adPack['floating'] ?? false) ? 'oc-ad-slot--floating' : '')
            . ($w > 0 && ! $isHomeBanner ? ' oc-ad-slot--sized mx-auto' : '')
            . ($isHomeBanner ? ' oc-ad-slot--home-banner w-full' : '')
        );
        $maxH = '';
        if ($h > 0) {
            $maxH = $isHomeBanner ? 'max-height: min('.$h.'px, 40vh);' : 'max-height: '.$h.'px;';
        }
        $mediaBoxStyle = $maxH . ($w > 0 ? ' max-width: 100%;' : '');
    @endphp
    <div class="{{ $wrapClass }}" data-oc-ad-slot="{{ $slotCode }}">
        <button
            type="button"
            class="oc-ad-slot__close"
            data-oc-ad-close
            aria-label="关闭此广告位"
            title="关闭（本机记住 24 小时）"
        >×</button>
        <div class="oc-ad-slot__box mx-auto flex items-center justify-center bg-black/5 min-h-0" style="{{ $boxStyle }}">
        @php
            $defLink = trim((string) ($slot->default_link_url ?? ''));
        @endphp
        @if ($defLink !== '')
            <a href="{{ $defLink }}" target="_blank" rel="noopener" class="block oc-ad-slot__link w-full h-full min-h-0 flex items-center justify-center">
                @if ($slot->default_image_url)
                    <img
                        src="{{ $slot->default_image_url }}"
                        alt="{{ $slot->default_title ?? '推广' }}"
                        class="oc-ad-slot__media w-full h-full max-w-full max-h-full object-contain"
                        style="{{ $mediaBoxStyle }}"
                        loading="lazy"
                    />
                @elseif ($slot->default_title)
                    <div class="p-4 text-sm oc-heading">{{ $slot->default_title }}</div>
                @elseif ($slot->default_content)
                    <div class="p-4 text-sm oc-heading oc-ad-slot__html">{!! $slot->default_content !!}</div>
                @endif
            </a>
        @else
            <div class="block oc-ad-slot__link oc-ad-slot__default-static w-full h-full min-h-0 flex flex-col items-center justify-center">
                @if ($slot->default_image_url)
                    <img
                        src="{{ $slot->default_image_url }}"
                        alt="{{ $slot->default_title ?? '推广' }}"
                        class="oc-ad-slot__media w-full h-full max-w-full max-h-full object-contain"
                        style="{{ $mediaBoxStyle }}"
                        loading="lazy"
                    />
                @endif
                @if ($slot->default_title)
                    <div class="p-4 text-sm oc-heading">{{ $slot->default_title }}</div>
                @endif
                @if ($slot->default_content)
                    <div class="p-4 text-sm oc-heading oc-ad-slot__html">{!! $slot->default_content !!}</div>
                @endif
            </div>
        @endif
        </div>
    </div>
    <script>
        (function () {
            var root = document.querySelector('[data-oc-ad-slot="{{ $slotCode }}"]');
            if (!root) return;
            var k = 'oc_ad_dismiss_{{ $slotCode }}';
            var day = 86400000;
            function until() {
                try { return parseInt(localStorage.getItem(k) || '0', 10) || 0; } catch (e) { return 0; }
            }
            if (Date.now() < until()) {
                root.remove();
                return;
            }
            var btn = root.querySelector('[data-oc-ad-close]');
            if (btn) {
                btn.addEventListener('click', function () {
                    try { localStorage.setItem(k, String(Date.now() + day)); } catch (e) {}
                    root.remove();
                });
            }
        })();
    </script>
@endif
