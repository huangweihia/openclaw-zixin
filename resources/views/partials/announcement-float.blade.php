@php
    $floatingAnnouncements = $floatingAnnouncements ?? collect();
@endphp
@if ($floatingAnnouncements->isNotEmpty())
    <div class="oc-announce-float-root" aria-label="浮动公告">
        @foreach ($floatingAnnouncements as $ann)
            @php
                $pos = in_array($ann->display_position, ['left', 'right', 'bottom'], true) ? $ann->display_position : 'right';
            @endphp
            <div
                class="oc-announce-float oc-announce-float--{{ $pos }}"
                data-oc-announce-float
                data-announce-id="{{ $ann->id }}"
            >
                <div class="oc-announce-float__wrap relative">
                    <button
                        type="button"
                        class="oc-announce-float__close"
                        data-oc-announce-float-close
                        aria-label="关闭浮动公告，10 分钟内不再显示"
                        title="关闭（10 分钟内不再显示）"
                    >
                        ×
                    </button>
                    @php
                        $fw = isset($ann->float_width) && $ann->float_width ? (int) $ann->float_width : null;
                        $fh = isset($ann->float_height) && $ann->float_height ? (int) $ann->float_height : null;
                        $cardInline = 'text-decoration: none;';
                        $cardInline .= $fw ? ' width: '.$fw.'px; max-width: '.$fw.'px;' : ' max-width: 220px;';
                    @endphp
                    <a href="{{ route('announcements.show', $ann) }}" class="oc-announce-float__card oc-surface block overflow-hidden rounded-xl shadow-lg" style="{{ $cardInline }}">
                        @if ($ann->cover_image)
                            <div class="w-full overflow-hidden block {{ $fh ? '' : 'h-28' }}" @if ($fh) style="height: {{ $fh }}px" @endif>
                                <img src="{{ $ann->cover_image }}" alt="" class="w-full h-full object-cover block" loading="lazy" />
                            </div>
                        @endif
                        <div class="p-3">
                            <span class="text-xs font-semibold oc-heading line-clamp-2">{{ $ann->title }}</span>
                        </div>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
    <script>
        (function () {
            var tenMin = 600000;
            document.querySelectorAll('[data-oc-announce-float]').forEach(function (root) {
                var id = root.getAttribute('data-announce-id') || '0';
                var k = 'oc_ann_float_until_' + id;
                function until() {
                    try {
                        return parseInt(localStorage.getItem(k) || '0', 10) || 0;
                    } catch (e) {
                        return 0;
                    }
                }
                if (Date.now() < until()) {
                    root.remove();
                    return;
                }
                var btn = root.querySelector('[data-oc-announce-float-close]');
                if (btn) {
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        try {
                            localStorage.setItem(k, String(Date.now() + tenMin));
                        } catch (err) {}
                        root.remove();
                    });
                }
            });
        })();
    </script>
@endif
