@extends('layouts.site')

@section('title', $tool->tool_name . ' — 工具变现')

@section('content')
    <div class="max-w-3xl mx-auto">
        <p class="mb-4">
            <a href="{{ route('tools.index') }}" class="oc-link text-sm" style="text-decoration: none;">← 工具地图</a>
        </p>
        <h1 class="text-2xl md:text-3xl font-bold oc-heading mb-2">{{ $tool->tool_name }}</h1>
        <p class="text-sm oc-muted mb-8">
            分类 {{ $tool->category }} · 定价 {{ $tool->pricing_model }}
            @if ($tool->available_in_china)
                · 国内可用
            @endif
        </p>

        @if (($canReadFull ?? true) && $tool->tool_url)
            <p class="mb-6">
                <a href="{{ $tool->tool_url }}" target="_blank" rel="noopener" class="btn btn-primary text-sm" style="text-decoration: none;">访问工具</a>
            </p>
        @endif

        <div class="oc-surface p-6 md:p-8 text-sm leading-relaxed" style="color: var(--dark);">
            @if ($canReadFull ?? true)
                <div class="article-content">{!! $tool->content !!}</div>
            @else
                @include('partials.gated-content-teaser', [
                    'teaserHtml' => $teaserHtml ?? '',
                    'mask' => $gateMask ?? [],
                ])
            @endif
        </div>
    </div>
@endsection
