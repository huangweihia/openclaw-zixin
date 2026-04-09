@include('partials.access-mask', [
    'level' => 'vip',
    'title' => $title ?? 'VIP 专属内容',
    'desc' => $desc ?? '开通 VIP 后可查看完整内容。',
    'cta' => $cta ?? '开通 VIP',
    'href' => $href ?? route('pricing'),
])
