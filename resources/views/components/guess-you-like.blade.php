@props([
    'excludeUserPostId' => null,
    'heading' => '猜你感兴趣',
    'description' => '基于热度与加热权重的用户投稿推荐。',
    'compact' => false,
    /** 嵌入侧栏等窄区域：去掉通栏大间距与顶部分割线 */
    'embedded' => false,
])

@php
    $guessSlot = 'gy' . bin2hex(random_bytes(4));
    $exclude = $excludeUserPostId !== null && $excludeUserPostId !== '' ? (int) $excludeUserPostId : null;
    $headingClass = $compact
        ? 'text-sm font-bold oc-heading mb-2'
        : 'text-lg font-bold oc-heading mb-1';
    $descClass = $compact
        ? 'text-xs oc-muted mb-3 m-0'
        : 'text-sm oc-muted mb-3 m-0';
    $sectionClasses = $embedded
        ? ['oc-guess-you-like', 'oc-guess-you-like--embedded']
        : ['oc-guess-you-like', 'mt-10', 'pt-8', 'border-t', 'oc-border'];
@endphp

<section
    {{ $attributes->class($sectionClasses) }}
    aria-labelledby="oc-guess-you-like-h-{{ $guessSlot }}"
>
    <h2 id="oc-guess-you-like-h-{{ $guessSlot }}" class="{{ $headingClass }}">{{ $heading }}</h2>
    @if ($description)
        <p class="{{ $descClass }}">{{ $description }}</p>
    @endif
    <ul
        class="js-oc-guess-you-like-list space-y-2 m-0 p-0 list-none text-sm min-h-[2rem]"
        data-exclude="{{ $exclude ?: '' }}"
    ></ul>
</section>

@once
    @push('scripts')
        <script>
            (function () {
                function esc(s) {
                    return String(s)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;');
                }
                function loadList(ul) {
                    if (ul.getAttribute('data-oc-guess-done') === '1') {
                        return;
                    }
                    ul.setAttribute('data-oc-guess-done', '1');
                    var ex = (ul.getAttribute('data-exclude') || '').trim();
                    var url = '/api/public/guess/user-posts' + (ex ? '?exclude_post_id=' + encodeURIComponent(ex) : '');
                    var token = document.querySelector('meta[name="csrf-token"]');
                    fetch(url, {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': token ? token.getAttribute('content') : '',
                        },
                        credentials: 'same-origin',
                    })
                        .then(function (r) {
                            return r.json();
                        })
                        .then(function (data) {
                            var items = data && data.items ? data.items : [];
                            if (!items.length) {
                                ul.innerHTML = '<li class="oc-muted">暂无可推荐投稿</li>';
                                return;
                            }
                            ul.innerHTML = items
                                .map(function (it) {
                                    return (
                                        '<li class="leading-snug"><a class="oc-link font-medium" style="text-decoration:none;" href="' +
                                        esc(it.url) +
                                        '">' +
                                        esc(it.title || '') +
                                        '</a></li>'
                                    );
                                })
                                .join('');
                        })
                        .catch(function () {
                            ul.innerHTML = '<li class="text-red-600">加载失败</li>';
                        });
                }
                function run() {
                    document.querySelectorAll('.js-oc-guess-you-like-list').forEach(loadList);
                }
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', run);
                } else {
                    run();
                }
            })();
        </script>
    @endpush
@endonce
