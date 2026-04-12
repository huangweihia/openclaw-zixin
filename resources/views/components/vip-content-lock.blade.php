@props([
    'href' => null,
    'cta' => '开通 VIP 阅读全文',
])
@php
    $href = $href ?? route('pricing');
@endphp
<div {{ $attributes->merge(['class' => 'absolute inset-0 z-20 flex items-center justify-center']) }}>
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-[2px]" style="border-radius: inherit;" aria-hidden="true"></div>
    <a href="{{ $href }}" class="relative z-10 btn btn-primary text-sm shadow-lg no-underline">{{ $cta }}</a>
</div>
