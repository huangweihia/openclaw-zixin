@extends('layouts.site')

@section('suppress_floating_promos')
@endsection

@section('title', $announcement->title . ' — 公告')

@section('content')
    <article class="max-w-3xl mx-auto oc-surface p-6 md:p-8">
        <header class="mb-6">
            <p class="text-xs oc-muted mb-2">
                {{ $announcement->published_at?->format('Y-m-d H:i') ?? $announcement->created_at->format('Y-m-d H:i') }}
                @if ($announcement->creator)
                    · {{ $announcement->creator->name }}
                @endif
            </p>
            <h1 class="text-2xl font-bold oc-heading m-0">{{ $announcement->title }}</h1>
        </header>
        <div class="article-content prose prose-slate max-w-none oc-announcement-body" style="color: var(--dark);">
            {!! $announcement->content !!}
        </div>
        <p class="mt-8 mb-0">
            <a href="{{ route('home') }}" class="oc-link text-sm">返回首页</a>
        </p>
    </article>
@endsection
