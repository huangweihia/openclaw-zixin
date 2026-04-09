@php
    $railAdPack = $railAdPack ?? null;
@endphp
@if ($railAdPack)
    @php
        $railPosition = $railPosition ?? 'left';
        $slotCode = $railAdPack['slot']->code ?? 'rail';
        $railSlot = $railAdPack['slot'];
        $rw = (int) ($railSlot->width ?? 0);
        $rh = (int) ($railSlot->height ?? 0);
        $railInnerStyle = 'width: ' . ($rw > 0 ? $rw : 140) . 'px;';
        if ($rh > 0) {
            $railInnerStyle .= ' max-height: ' . $rh . 'px; overflow: hidden;';
        }
        $railMediaClass = 'w-full h-full max-w-full object-contain ' . ($rh > 0 ? '' : 'max-h-64');
    @endphp
    <aside class="oc-ad-rail oc-ad-rail--{{ $railPosition }} hidden lg:block" data-oc-ad-rail="{{ $railPosition }}" aria-label="侧栏推广">
        <div class="oc-ad-rail__inner oc-surface rounded-xl overflow-hidden shadow-sm flex flex-col items-stretch" style="{{ $railInnerStyle }}">
            @php
                $slot = $railAdPack['slot'];
                $defLink = trim((string) ($slot->default_link_url ?? ''));
            @endphp
            @if ($defLink !== '')
                <a href="{{ $defLink }}" target="_blank" rel="noopener" class="block min-h-0 flex-1 flex items-center justify-center">
                    @if ($slot->default_image_url)
                        <img src="{{ $slot->default_image_url }}" alt="{{ $slot->default_title ?? '' }}" class="{{ $railMediaClass }}" style="{{ $rh > 0 ? 'max-height:'.$rh.'px' : '' }}" loading="lazy" />
                    @elseif ($slot->default_title)
                        <div class="p-3 text-xs oc-heading">{{ $slot->default_title }}</div>
                    @elseif ($slot->default_content)
                        <div class="p-3 text-xs oc-heading">{!! $slot->default_content !!}</div>
                    @endif
                </a>
            @else
                <div class="block min-h-0 flex-1 flex flex-col items-center justify-center">
                    @if ($slot->default_image_url)
                        <img src="{{ $slot->default_image_url }}" alt="{{ $slot->default_title ?? '' }}" class="{{ $railMediaClass }}" style="{{ $rh > 0 ? 'max-height:'.$rh.'px' : '' }}" loading="lazy" />
                    @endif
                    @if ($slot->default_title)
                        <div class="p-3 text-xs oc-heading">{{ $slot->default_title }}</div>
                    @endif
                    @if ($slot->default_content)
                        <div class="p-3 text-xs oc-heading">{!! $slot->default_content !!}</div>
                    @endif
                </div>
            @endif
        </div>
    </aside>
@endif
