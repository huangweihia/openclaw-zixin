@extends('layouts.site')

@section('title', '个人中心 — OpenClaw 智信')

@section('content')
    @php
        $u = auth()->user();
    @endphp

    <h1 class="text-3xl font-bold text-center mb-8 oc-heading">👤 个人中心</h1>
    <div class="grid lg:grid-cols-12 gap-6 items-start">
        <aside class="lg:col-span-3 oc-surface p-4">
            <h3 class="text-sm font-bold oc-heading mb-3">菜单</h3>
            <nav class="space-y-2">
                <a href="#dash-profile" class="block text-sm oc-link">个人资料</a>
                <a href="#dash-subscription" class="block text-sm oc-link">会员与订阅</a>
                <a href="#dash-quick" class="block text-sm oc-link">快捷入口</a>
                <a href="#dash-timeline" class="block text-sm oc-link">最近动态</a>
            </nav>
        </aside>
        <div class="lg:col-span-9 space-y-8">

    {{-- 原型：用户信息卡片 + 编辑入口 + 统计 --}}
    <div id="dash-profile" class="oc-surface p-6 md:p-8">
        <div class="flex flex-col sm:flex-row sm:items-start gap-6">
            <div class="shrink-0 mx-auto sm:mx-0 text-center sm:text-left">
                @if ($u->avatar)
                    <img src="{{ $u->avatar }}" alt="" class="w-20 h-20 md:w-24 md:h-24 rounded-full object-cover border-2 mx-auto sm:mx-0" style="border-color: rgba(148,163,184,.35);" />
                @else
                    <div class="w-20 h-20 md:w-24 md:h-24 rounded-full mx-auto sm:mx-0 flex items-center justify-center text-2xl font-bold text-white" style="background: var(--gradient-primary);">
                        {{ mb_substr($u->name, 0, 1) }}
                    </div>
                @endif
            </div>
            <div class="flex-1 min-w-0 text-center sm:text-left">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-2 w-full">
                    <h2 class="text-xl md:text-2xl font-bold oc-heading m-0">{{ $u->name }}</h2>
                    <a href="{{ route('dashboard.edit') }}" class="btn btn-secondary text-sm shrink-0">编辑</a>
                </div>
                <p class="text-sm oc-muted mb-1">{{ $handle }}</p>
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-x-3 gap-y-1 text-sm">
                    <span class="font-semibold oc-heading">{{ $roleLabel }}</span>
                    @if ($vipDays !== null)
                        <span class="{{ !empty($vipIsUrgent) ? 'text-red-600 font-semibold' : 'oc-muted' }}">
                            剩余 <strong class="oc-heading">{{ $vipDays }}</strong> 天
                        </span>
                        @if (!empty($vipIsUrgent) && !empty($vipSecondsLeft) && $vipSecondsLeft > 0)
                            <span
                                id="vip-expiry-countdown"
                                class="text-xs px-2 py-1 rounded-full"
                                style="background: #fee2e2; color: #b91c1c;"
                                data-left="{{ (int) $vipSecondsLeft }}"
                            >倒计时加载中...</span>
                        @endif
                    @elseif ($vipExpiresAt)
                        <span class="oc-muted">有效期至 {{ $vipExpiresAt->format('Y-m-d H:i') }}</span>
                    @endif
                </div>
                @if ($u->bio)
                    <p class="text-sm oc-muted mt-3 line-clamp-2">{{ $u->bio }}</p>
                @endif
            </div>
        </div>

        <div class="mt-8 pt-6 border-t oc-border">
            <h3 class="text-sm font-bold oc-heading mb-4">📊 统计数据</h3>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="oc-stat-card rounded-xl p-4 text-center">
                    <div class="text-lg mb-1">📝</div>
                    <div class="text-xs oc-muted">发布数</div>
                    <div class="text-xl font-bold oc-heading">{{ number_format($postsCount) }}</div>
                </div>
                <div class="oc-stat-card rounded-xl p-4 text-center">
                    <div class="text-lg mb-1">👁️</div>
                    <div class="text-xs oc-muted">内容被浏览</div>
                    <div class="text-xl font-bold oc-heading">{{ number_format($viewsCount) }}</div>
                    <div class="text-[11px] oc-muted mt-1">足迹 {{ number_format($footprintCount) }} 条</div>
                </div>
                <div class="oc-stat-card rounded-xl p-4 text-center">
                    <div class="text-lg mb-1">⭐</div>
                    <div class="text-xs oc-muted">收藏数</div>
                    <div class="text-xl font-bold oc-heading">{{ number_format($favoritesCount) }}</div>
                </div>
                <div class="oc-stat-card rounded-xl p-4 text-center">
                    <div class="text-lg mb-1">💬</div>
                    <div class="text-xs oc-muted">评论数</div>
                    <div class="text-xl font-bold oc-heading">{{ number_format($commentsCount) }}</div>
                </div>
            </div>
        </div>
    </div>

    @if (! $u->isAdmin())
        <div class="max-w-4xl mx-auto mb-8">
            @include('partials.membership-compare')
        </div>
    @endif

    {{-- 订阅（独立条，原型外补充） --}}
    <div id="dash-subscription" class="oc-surface p-6">
        <h3 class="font-bold oc-heading mb-3">订阅</h3>
        @if ($u->isAdmin())
            <p class="text-sm oc-muted mb-4">超级管理员享有全站权益，无需开通 VIP。</p>
            <a href="{{ route('pricing') }}" class="btn btn-secondary text-sm">查看价格页（演示）</a>
        @else
            <p class="text-sm oc-muted mb-4">续费或升级请在价格页完成支付。</p>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('pricing') }}" class="btn btn-primary text-sm">价格与套餐</a>
                <a href="{{ route('payments.confirm', ['plan' => 'vip']) }}" class="btn btn-secondary text-sm">去支付页</a>
            </div>
        @endif
        @if (in_array($u->role, ['vip', 'svip', 'admin'], true))
            <div class="mt-4 pt-4 border-t oc-border">
                <h4 class="text-sm font-semibold oc-heading mb-2">邮件订阅（可选内容 + 发送时间）</h4>
                <form id="email-sub-form" class="space-y-3">
                    <input type="email" id="email-sub-input" class="oc-input" style="max-width:320px" value="{{ $u->email }}" required />
                    <div class="grid sm:grid-cols-2 gap-3">
                        @php
                            $topicLabels = ['daily' => '每日精选', 'weekly' => '每周精选', 'notification' => '系统通知', 'promotion' => '活动推广'];
                            $selectedTopics = $emailSubscription->subscribed_to ?? ['notification'];
                            $topicSchedule = $emailSubscription->topic_schedule ?? [];
                        @endphp
                        @foreach ($topicLabels as $topicKey => $topicLabel)
                            <label class="text-sm oc-muted flex items-center gap-2 flex-wrap">
                                <input type="checkbox" class="email-sub-topic" value="{{ $topicKey }}" {{ in_array($topicKey, $selectedTopics, true) ? 'checked' : '' }} />
                                <span class="min-w-[90px]">{{ $topicLabel }}</span>
                                <input
                                    type="time"
                                    class="oc-input email-sub-time"
                                    data-topic="{{ $topicKey }}"
                                    value="{{ $topicSchedule[$topicKey] ?? '09:00' }}"
                                    style="max-width:140px"
                                />
                            </label>
                        @endforeach
                    </div>
                    <button type="submit" class="btn btn-secondary text-sm">保存订阅设置</button>
                </form>
                <p id="email-sub-msg" class="text-xs oc-muted mt-2 mb-0"></p>
            </div>
        @endif
    </div>

    {{-- 快捷入口 --}}
    <div id="dash-quick">
        <h3 class="text-sm font-bold oc-heading mb-4">快捷入口</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <a href="{{ route('user-posts.index') }}" class="oc-quick-tile flex flex-col items-center justify-center gap-2 p-5 rounded-xl text-center">
                <span class="text-2xl" aria-hidden="true">📝</span>
                <span class="text-sm font-semibold oc-heading">我的发布</span>
            </a>
            <a href="{{ route('favorites.index') }}" class="oc-quick-tile flex flex-col items-center justify-center gap-2 p-5 rounded-xl text-center">
                <span class="text-2xl" aria-hidden="true">⭐</span>
                <span class="text-sm font-semibold oc-heading">我的收藏</span>
            </a>
            <a href="{{ route('history.index') }}" class="oc-quick-tile flex flex-col items-center justify-center gap-2 p-5 rounded-xl text-center">
                <span class="text-2xl" aria-hidden="true">👣</span>
                <span class="text-sm font-semibold oc-heading">浏览历史</span>
            </a>
            <a href="{{ route('dashboard.comments') }}" class="oc-quick-tile flex flex-col items-center justify-center gap-2 p-5 rounded-xl text-center">
                <span class="text-2xl" aria-hidden="true">💬</span>
                <span class="text-sm font-semibold oc-heading">我的评论</span>
            </a>
            <a href="{{ route('dashboard.orders') }}" class="oc-quick-tile flex flex-col items-center justify-center gap-2 p-5 rounded-xl text-center">
                <span class="text-2xl" aria-hidden="true">💳</span>
                <span class="text-sm font-semibold oc-heading">我的订单</span>
            </a>
            @if ($u->role === 'svip' || $u->isAdmin())
                <a href="{{ route('svip-subscriptions.index') }}" class="oc-quick-tile flex flex-col items-center justify-center gap-2 p-5 rounded-xl text-center">
                    <span class="text-2xl" aria-hidden="true">✨</span>
                    <span class="text-sm font-semibold oc-heading">SVIP 定制</span>
                </a>
            @endif
        </div>
    </div>

    {{-- 最近动态 --}}
    <div id="dash-timeline" class="oc-surface p-6 md:p-8">
        <h3 class="text-lg font-bold oc-heading mb-4">最近动态</h3>
        <p class="text-xs oc-muted mb-4">时间线</p>
        @if ($timeline->isEmpty())
            <p class="text-sm oc-muted m-0">暂无动态，去发布内容或参与互动吧。</p>
        @else
            <ul class="space-y-0 m-0 p-0 list-none border-l-2 pl-4 oc-timeline-border">
                @foreach ($timeline as $row)
                    <li class="relative pb-5 pl-2 oc-timeline-item">
                        <span class="absolute -left-[9px] top-1.5 w-2 h-2 rounded-full oc-timeline-dot" aria-hidden="true"></span>
                        <time class="text-xs oc-muted block mb-1">{{ $row['at']->format('Y-m-d H:i') }}</time>
                        <span class="text-sm oc-heading">{{ $row['text'] }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const el = document.getElementById('vip-expiry-countdown');
            if (!el) return;
            let left = Number(el.getAttribute('data-left') || 0);
            const pad = (n) => String(n).padStart(2, '0');
            const render = () => {
                if (!Number.isFinite(left) || left <= 0) {
                    el.textContent = '已到期，请尽快续费';
                    return;
                }
                const d = Math.floor(left / 86400);
                const h = Math.floor((left % 86400) / 3600);
                const m = Math.floor((left % 3600) / 60);
                const s = Math.floor(left % 60);
                el.textContent = `剩余 ${d}天 ${pad(h)}:${pad(m)}:${pad(s)}`;
            };
            render();
            setInterval(function () {
                left -= 1;
                render();
            }, 1000);
        })();
    </script>
    @if (in_array($u->role, ['vip', 'svip', 'admin'], true))
        <script>
            (function () {
                const form = document.getElementById('email-sub-form');
                if (!form) return;
                const input = document.getElementById('email-sub-input');
                const msg = document.getElementById('email-sub-msg');
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                form.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    const email = (input?.value || '').trim();
                    if (!email) return;
                    const checked = Array.from(form.querySelectorAll('.email-sub-topic:checked')).map((x) => x.value);
                    if (!checked.length) {
                        msg.textContent = '请至少选择一个订阅内容';
                        msg.style.color = '#b91c1c';
                        return;
                    }
                    const schedule = {};
                    form.querySelectorAll('.email-sub-time').forEach((el) => {
                        const topic = el.getAttribute('data-topic');
                        if (!topic) return;
                        schedule[topic] = (el.value || '09:00').slice(0, 5);
                    });
                    try {
                        const res = await fetch('/api/email-subscriptions', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                email,
                                subscribed_to: checked,
                                topic_schedule: schedule,
                            }),
                        });
                        const data = await res.json().catch(() => ({}));
                        msg.textContent = res.ok ? '订阅成功，后续可在后台邮件订阅管理查看。' : (data.message || '订阅失败，请稍后重试');
                        msg.style.color = res.ok ? '#166534' : '#b91c1c';
                    } catch (err) {
                        msg.textContent = '网络异常，请稍后重试';
                        msg.style.color = '#b91c1c';
                    }
                });
            })();
        </script>
    @endif
@endpush
