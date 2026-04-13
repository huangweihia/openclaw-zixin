@extends('layouts.site')

@section('title', $title . ' — OpenClaw 智信')

@section('content')
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold oc-heading mb-2">{{ $title }}</h1>
        <p class="text-sm oc-muted mb-6 m-0">点击头像可查看对方简介；在资料弹窗中可关注或取消关注。</p>

        <div class="oc-surface p-6">
            @if ($users->isEmpty())
                <p class="text-sm oc-muted m-0">{{ $emptyText }}</p>
            @else
                <ul class="space-y-3 m-0 p-0 list-none">
                    @foreach ($users as $row)
                        <li class="flex items-center gap-3 border-b oc-divide pb-3 last:border-0 last:pb-0">
                            <button
                                type="button"
                                class="shrink-0 p-0 border-0 bg-transparent cursor-pointer rounded-full"
                                data-oc-user-card="{{ $row->id }}"
                                aria-label="查看 {{ $row->name }} 的资料"
                            >
                                @if (! empty($row->avatar))
                                    <img src="{{ $row->avatar }}" alt="" class="w-10 h-10 rounded-full object-cover" loading="lazy" />
                                @else
                                    <span
                                        class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold text-white"
                                        style="background: var(--gradient-primary);"
                                    >{{ mb_substr($row->name ?? '?', 0, 1) }}</span>
                                @endif
                            </button>
                            <div class="min-w-0 flex-1">
                                <button
                                    type="button"
                                    class="font-semibold oc-heading text-left w-full border-0 bg-transparent cursor-pointer p-0 truncate"
                                    data-oc-user-card="{{ $row->id }}"
                                >
                                    {{ $row->name }}
                                </button>
                                @if (! empty($row->bio))
                                    <p class="text-xs oc-muted m-0 mt-0.5 line-clamp-2">{{ $row->bio }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-6">{{ $users->links() }}</div>
            @endif
        </div>
    </div>
@endsection
