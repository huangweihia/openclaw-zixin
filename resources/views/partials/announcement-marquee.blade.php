@php
    $marqueeAnnouncements = $marqueeAnnouncements ?? collect();
    $marqueePlacement = $placement ?? 'top';
@endphp
@if ($marqueeAnnouncements->isNotEmpty())
    <div
        class="oc-announcement-bar oc-announcement-bar--{{ $marqueePlacement }}"
        role="region"
        aria-label="站点公告"
        data-oc-announcement-bar
        data-oc-placement="{{ $marqueePlacement }}"
    >
        <div class="oc-announcement-bar__inner max-w-7xl mx-auto px-4 flex items-center gap-3 py-2">
            <span class="oc-announcement-bar__tag shrink-0 text-xs font-semibold px-2 py-0.5 rounded">公告</span>
            <div class="oc-announcement-bar__track flex-1 min-w-0 overflow-hidden">
                <div class="oc-announcement-bar__scroll flex flex-nowrap items-center">
                    {{-- 仅一条公告时复制多段以保证无缝右→左滚动 --}}
                    @foreach ([1, 2] as $_loop)
                        @foreach ($marqueeAnnouncements as $ann)
                            @foreach (range(1, 6) as $_r)
                                <a href="{{ route('announcements.show', $ann) }}" class="oc-announcement-bar__link whitespace-nowrap shrink-0">
                                    {{ $ann->title }}
                                </a>
                                <span class="oc-announcement-bar__sep mx-6 opacity-50 shrink-0" aria-hidden="true">·</span>
                            @endforeach
                        @endforeach
                    @endforeach
                </div>
            </div>
            <button
                type="button"
                class="oc-announcement-bar__close shrink-0"
                data-oc-announcement-close
                aria-label="关闭公告，10 分钟内不再显示"
                title="关闭（10 分钟内不再显示）"
            >
                ×
            </button>
        </div>
    </div>
    <script>
        (function () {
            var bar = document.querySelector('[data-oc-announcement-bar][data-oc-placement="{{ $marqueePlacement }}"]');
            if (!bar) return;
            var k = 'oc_ann_bar_until_{{ $marqueePlacement }}';
            var tenMin = 600000;
            function until() {
                try {
                    return parseInt(localStorage.getItem(k) || '0', 10) || 0;
                } catch (e) {
                    return 0;
                }
            }
            if (Date.now() < until()) {
                bar.remove();
                return;
            }
            var btn = bar.querySelector('[data-oc-announcement-close]');
            if (btn) {
                btn.addEventListener('click', function () {
                    try {
                        localStorage.setItem(k, String(Date.now() + tenMin));
                    } catch (e) {}
                    bar.remove();
                });
            }
        })();
    </script>
@endif
