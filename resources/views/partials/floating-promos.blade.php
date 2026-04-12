{{-- 全站只展示一类「角标浮动」：优先浮动公告，否则「浮动」类广告位，避免左下角/右下角各一块重复素材 --}}
@if ($floatingAnnouncements->isNotEmpty())
    @include('partials.announcement-float')
@elseif (! empty($floatingAdPacks))
    @include('partials.floating-ads')
@endif
