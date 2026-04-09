@extends('layouts.site')

@section('title', $case->title . ' — 案例')

@section('content')
    <article class="max-w-3xl mx-auto">
        <p class="mb-4">
            <a href="{{ route('cases.index') }}" class="oc-link text-sm" style="text-decoration: none;">← 案例列表</a>
        </p>
        <header class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold oc-heading mb-2">{{ $case->title }}</h1>
            <p class="text-sm oc-muted m-0">
                {{ $case->category }} · {{ $case->type }} · 启动成本 {{ $case->startup_cost }} · {{ $case->time_investment }}
            </p>
        </header>

        <div class="oc-surface p-6 md:p-8 space-y-6">
            <section class="article-content text-sm leading-relaxed" style="color: var(--dark);">
                {!! $bodyHtml !!}
            </section>
            @if ($stepsHtml)
                <section>
                    <h2 class="text-lg font-bold oc-heading mb-3">操作步骤</h2>
                    <div class="article-content text-sm leading-relaxed" style="color: var(--dark);">
                        {!! $stepsHtml !!}
                    </div>
                </section>
            @endif
        </div>
    </article>
@endsection
