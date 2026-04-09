@extends('layouts.site')

@section('title', '通知中心 — OpenClaw 智信')

@section('content')
    @php
        $unreadCount = auth()->user()->inboxNotifications()->where('is_read', false)->count();
    @endphp
    <div class="max-w-3xl mx-auto">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <h1 class="text-2xl font-bold oc-heading m-0">
                通知中心
                @if ($unreadCount > 0)
                    <span class="text-base font-semibold oc-muted">（{{ $unreadCount }} 未读）</span>
                @endif
            </h1>
            @if ($items->total() > 0)
                <form method="post" action="{{ route('notifications.read-all') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-secondary text-sm">全部已读</button>
                </form>
            @endif
        </div>

        <form method="get" action="{{ route('notifications.index') }}" class="flex flex-wrap gap-3 items-center mb-6 oc-surface p-3 rounded-xl">
            <label class="text-sm oc-muted flex items-center gap-2 m-0 cursor-pointer">
                <input type="checkbox" name="unread" value="1" @checked(request('unread') === '1') onchange="this.form.submit()" />
                仅未读
            </label>
            <input
                type="search"
                name="q"
                value="{{ request('q') }}"
                placeholder="搜索标题或正文…"
                class="oc-input flex-1 min-w-[160px] text-sm"
            />
            <button type="submit" class="btn btn-primary text-sm">搜索</button>
            @if (request()->anyFilled(['q', 'unread']))
                <a href="{{ route('notifications.index') }}" class="oc-link text-sm">重置</a>
            @endif
        </form>

        <div class="oc-surface divide-y oc-divide">
            @forelse ($items as $n)
                <article class="p-4 md:p-5 hover:bg-slate-50/80 transition oc-notif-row {{ $n->is_read ? 'opacity-90' : '' }}">
                    <div class="flex gap-3">
                        @if (! $n->is_read)
                            <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 oc-notif-dot" aria-hidden="true"></span>
                        @else
                            <span class="w-2 shrink-0" aria-hidden="true"></span>
                        @endif
                        <div class="flex-1 min-w-0">
                            <h2 class="text-base font-semibold oc-heading m-0 mb-1">{{ $n->title }}</h2>
                            @if ($n->content)
                                <p class="text-sm oc-muted m-0 mb-3 whitespace-pre-wrap">{{ $n->content }}</p>
                            @endif
                            <time class="text-xs oc-muted">{{ $n->created_at->format('Y-m-d H:i') }}</time>
                            <div class="flex flex-wrap gap-3 mt-3">
                                @if ($n->action_url)
                                    <a href="{{ route('notifications.open', $n) }}" class="oc-link text-sm font-medium" style="text-decoration: none;">查看详情</a>
                                @endif
                                @if (! $n->is_read)
                                    <form method="post" action="{{ route('notifications.read', $n) }}" class="inline m-0">
                                        @csrf
                                        <button type="submit" class="oc-link text-sm font-medium bg-transparent border-0 cursor-pointer p-0" style="text-decoration: none;">标为已读</button>
                                    </form>
                                @endif
                                <form method="post" action="{{ route('notifications.destroy', $n) }}" class="inline m-0" onsubmit="return confirm('删除这条通知？');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:underline bg-transparent border-0 cursor-pointer p-0">删除</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <p class="p-8 text-center text-sm oc-muted m-0">暂无通知</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $items->links() }}</div>
    </div>
@endsection
