@php
    $floatingAdPacks = $floatingAdPacks ?? [];
@endphp
@if (! empty($floatingAdPacks))
    <div class="oc-floating-ads" aria-label="浮动推广（由「浮动」类广告位承载，非单条广告开关）">
        @foreach ($floatingAdPacks as $idx => $pack)
            @php
                $slot = $pack['slot'];
                $w = max(80, (int) ($slot->width ?? 120));
                $h = max(80, (int) ($slot->height ?? 160));
                $style = 'position:fixed;z-index:40;bottom:' . (6 + $idx * 7) . 'rem;right:1rem;width:' . $w . 'px;max-height:' . $h . 'px;';
                $slotKey = 'float-' . ($slot->code ?? $slot->id) . '-' . $idx;
            @endphp
            <div class="oc-floating-ad-wrap" style="{{ $style }}" data-oc-float-ad="{{ e($slotKey) }}">
                <button
                    type="button"
                    class="oc-floating-ad__close"
                    data-oc-float-close
                    aria-label="关闭浮动推广"
                    title="关闭（24 小时内不再显示）"
                >×</button>
                @php
                    $defLink = trim((string) ($slot->default_link_url ?? ''));
                @endphp
                @if ($defLink !== '')
                    <a
                        href="{{ $defLink }}"
                        target="_blank"
                        rel="noopener"
                        class="oc-floating-ad rounded-xl overflow-hidden shadow-xl border oc-border block bg-[var(--white,#fff)]"
                    >
                        @if ($slot->default_image_url)
                            <img src="{{ $slot->default_image_url }}" alt="{{ $slot->default_title ?? '' }}" class="w-full object-contain max-h-40 block" style="max-height: {{ $h - 28 }}px" loading="lazy" />
                        @endif
                        <span class="block text-[10px] px-2 py-1 oc-heading truncate bg-[var(--white,#fff)]">{{ $slot->default_title ?? '推广' }}</span>
                    </a>
                @else
                    <div class="oc-floating-ad rounded-xl overflow-hidden shadow-xl border oc-border bg-[var(--white,#fff)]">
                        @if ($slot->default_image_url)
                            <img src="{{ $slot->default_image_url }}" alt="{{ $slot->default_title ?? '' }}" class="w-full object-contain max-h-40 block" style="max-height: {{ $h - 28 }}px" loading="lazy" />
                        @endif
                        <span class="block text-[10px] px-2 py-1 oc-heading truncate bg-[var(--white,#fff)]">{{ $slot->default_title ?? '推广' }}</span>
                    </div>
                @endif
            </div>
            <script>
                (function () {
                    var root = document.querySelector('[data-oc-float-ad="{{ $slotKey }}"]');
                    if (!root) return;
                    var k = 'oc_float_dismiss_{{ md5($slotKey) }}';
                    var day = 86400000;
                    function until() {
                        try { return parseInt(localStorage.getItem(k) || '0', 10) || 0; } catch (e) { return 0; }
                    }
                    if (Date.now() < until()) {
                        root.remove();
                        return;
                    }
                    var btn = root.querySelector('[data-oc-float-close]');
                    if (btn) {
                        btn.addEventListener('click', function () {
                            try { localStorage.setItem(k, String(Date.now() + day)); } catch (e) {}
                            root.remove();
                        });
                    }
                })();
            </script>
        @endforeach
    </div>
@endif
