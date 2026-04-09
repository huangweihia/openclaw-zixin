@include('partials.access-mask', [
    'level' => 'svip',
    'title' => $title ?? 'SVIP 专属内容',
    'desc' => $desc ?? '升级 SVIP 后可查看此内容。',
    'cta' => $cta ?? '升级 SVIP',
    'href' => $href ?? route('pricing'),
])
