@props([
    'href' => null,
    'cta' => '开通 VIP 阅读全文',
    'title' => 'VIP 专属内容',
    'desc' => '开通 VIP 后可查看完整内容。',
    'level' => 'vip',
])
@include('partials.access-mask', [
    'level' => $level,
    'title' => $title,
    'desc' => $desc,
    'cta' => $cta,
    'href' => $href ?? route('pricing'),
])
