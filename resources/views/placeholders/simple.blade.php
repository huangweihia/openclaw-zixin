@extends('layouts.site')

@section('title', ($title ?? '页面') . ' — OpenClaw 智信')

@section('content')
    <div class="max-w-3xl mx-auto oc-surface p-10 text-center">
        <h1 class="text-2xl font-bold mb-4" style="color: var(--dark);">{{ $title ?? '页面' }}</h1>
        <p class="text-sm leading-relaxed" style="color: var(--gray);">{{ $description ?? '内容建设中，敬请期待。' }}</p>
        <a href="{{ route('home') }}" class="btn btn-primary mt-8 inline-flex">返回首页</a>
    </div>
@endsection
