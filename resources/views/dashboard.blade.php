@extends('layouts.site')

@section('title', '个人中心 — OpenClaw 智信')

@section('content')
    @php
        $u = auth()->user();
    @endphp

    <h1 class="text-3xl font-bold text-center mb-8 oc-heading">👤 个人中心</h1>

    <div
        id="oc-dashboard-shell"
        class="flex flex-col lg:flex-row gap-6 items-start"
        style="--ocDashLeft: 320px;"
    >
        {{-- 左侧：快捷菜单（宽度可调节） --}}
        <aside
            id="oc-dash-left"
            class="w-full lg:sticky lg:top-20 oc-surface p-4"
            style="width: var(--ocDashLeft); max-width: 520px;"
        >
            <h3 class="text-lg font-bold oc-heading mb-4 tracking-tight">个人中心</h3>
            <nav class="space-y-0.5" aria-label="个人中心菜单">
                <p class="text-sm font-bold oc-muted mb-2 px-1 tracking-wide" style="letter-spacing: 0.04em;">常用</p>
                <button type="button" class="oc-dash-tab w-full text-left px-2.5 py-3 rounded-lg text-[17px] leading-snug font-semibold oc-heading" data-tab="profile" style="background: rgba(148,163,184,.12);">
                    个人资料
                </button>
                <button type="button" class="oc-dash-tab w-full text-left px-2.5 py-3 rounded-lg text-[17px] leading-snug oc-link" data-tab="subscription">
                    会员与订阅
                </button>
                <button type="button" class="oc-dash-tab w-full text-left px-2.5 py-3 rounded-lg text-[17px] leading-snug oc-link" data-tab="timeline">
                    最近动态
                </button>
                <button type="button" class="oc-dash-tab w-full text-left px-2.5 py-3 rounded-lg text-[17px] leading-snug oc-link mt-3" data-tab="posts">
                    📝 我的发布
                </button>
                <button type="button" class="oc-dash-tab w-full text-left px-2.5 py-3 rounded-lg text-[17px] leading-snug oc-link" data-tab="favorites">
                    ⭐ 我的收藏
                </button>
                <button type="button" class="oc-dash-tab w-full text-left px-2.5 py-3 rounded-lg text-[17px] leading-snug oc-link" data-tab="history">
                    👣 浏览历史
                </button>
                <button type="button" class="oc-dash-tab w-full text-left px-2.5 py-3 rounded-lg text-[17px] leading-snug oc-link" data-tab="comments">
                    💬 我的评论
                </button>
                <button type="button" class="oc-dash-tab w-full text-left px-2.5 py-3 rounded-lg text-[17px] leading-snug oc-link" data-tab="orders">
                    💳 我的订单
                </button>
                <button type="button" class="oc-dash-tab w-full text-left px-2.5 py-3 rounded-lg text-[17px] leading-snug oc-link" data-tab="points">
                    🪙 积分与充值
                </button>
                @if ($u->role === 'svip' || $u->isAdmin())
                    <button type="button" class="oc-dash-tab w-full text-left px-2.5 py-3 rounded-lg text-[17px] leading-snug oc-link" data-tab="svip">
                        ✨ SVIP 定制
                    </button>
                @endif
            </nav>
        </aside>

        {{-- 左侧宽度拖拽条（仅桌面端） --}}
        <div
            id="oc-dash-resizer"
            class="hidden lg:block self-stretch"
            style="width: 10px; margin-left: -6px; cursor: col-resize;"
            aria-hidden="true"
            title="拖拽调整左侧宽度"
        >
            <div class="h-full w-[2px] mx-auto" style="background: rgba(148,163,184,.35); border-radius: 999px;"></div>
        </div>

        {{-- 右侧：内容区（宽度可调节，随左侧变化自适应） --}}
        <section id="oc-dash-right" class="flex-1 min-w-0 w-full space-y-6">
            {{-- 个人资料（默认面板：包含顶部主卡片 + 统计，占据右侧顶部） --}}
            <div id="dash-profile" class="oc-dash-panel oc-surface p-6 md:p-8" data-panel="profile">
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
                            <a href="#dash-profile-edit" class="btn btn-secondary text-sm shrink-0" style="text-decoration:none;">编辑资料</a>
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
                    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
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
                        <div class="oc-stat-card rounded-xl p-4 text-center">
                            <div class="text-lg mb-1">🪙</div>
                            <div class="text-xs oc-muted">积分</div>
                            <div class="text-xl font-bold oc-heading">{{ number_format((int) ($u->points_balance ?? 0)) }}</div>
                        </div>
                    </div>
                </div>

                <div id="dash-guess" class="mt-8 pt-6 border-t oc-border">
                    <h3 class="text-sm font-bold oc-heading mb-2">猜你感兴趣</h3>
                    <p class="text-xs oc-muted mb-3 m-0">结合加热权重与热度推荐投稿；列表由接口随机生成。</p>
                    <ul id="dash-guess-list" class="space-y-2 m-0 p-0 list-none text-sm oc-muted min-h-[2rem]"></ul>
                </div>

                <div id="dash-profile-edit" class="mt-8 pt-6 border-t oc-border scroll-mt-24">
                    <h3 class="text-sm font-bold oc-heading mb-4">编辑资料</h3>
                    @include('partials.dashboard-profile-forms')
                </div>
            </div>

    {{-- 订阅（独立条，原型外补充） --}}
    <div id="dash-subscription" class="oc-dash-panel oc-surface p-6 hidden" data-panel="subscription">
        <h3 class="font-bold oc-heading mb-3">订阅</h3>
        @if (! $u->isAdmin())
            <div class="max-w-4xl mx-auto mb-6">
                @include('partials.membership-compare')
            </div>
        @endif
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
        @if ($u->canAccessVipExclusiveContent())
            <div class="mt-4 pt-4 border-t oc-border">
                <h4 class="text-sm font-semibold oc-heading mb-2">邮件订阅（可选内容 + 发送时间）</h4>
                <form id="email-sub-form" class="space-y-3">
                    <input type="email" id="email-sub-input" class="oc-input" style="max-width:320px" value="{{ $u->email }}" readonly tabindex="-1" aria-readonly="true" />
                    <p class="text-xs oc-muted m-0">收件邮箱与账号绑定；修改请通过下方「修改绑定邮箱」完成。</p>
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

    {{-- 最近动态（含原首页「全部动态」会员开通流水） --}}
    <div id="dash-timeline" class="oc-dash-panel oc-surface p-6 md:p-8 hidden" data-panel="timeline">
        <h3 class="text-lg font-bold oc-heading mb-4">最近动态</h3>
        <p class="text-xs oc-muted mb-4">与你相关的时间线</p>
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

    @php
        $embedQs = '?oc_embed=1';
    @endphp
    <div class="oc-dash-panel oc-surface overflow-hidden hidden flex flex-col" data-panel="posts" style="min-height: 70vh;">
        <div class="flex items-center gap-2 px-4 py-2 border-b oc-border shrink-0" style="background: rgba(148,163,184,.08);">
            <span class="text-sm font-semibold oc-heading">我的发布</span>
        </div>
        <iframe class="w-full flex-1 border-0 min-h-[65vh]" title="我的发布" data-embed-src="{{ route('user-posts.index') }}{{ $embedQs }}"></iframe>
    </div>
    <div class="oc-dash-panel oc-surface overflow-hidden hidden flex flex-col" data-panel="favorites" style="min-height: 70vh;">
        <div class="flex items-center gap-2 px-4 py-2 border-b oc-border shrink-0" style="background: rgba(148,163,184,.08);">
            <span class="text-sm font-semibold oc-heading">我的收藏</span>
        </div>
        <iframe class="w-full flex-1 border-0 min-h-[65vh]" title="我的收藏" data-embed-src="{{ route('favorites.index') }}{{ $embedQs }}"></iframe>
    </div>
    <div class="oc-dash-panel oc-surface overflow-hidden hidden flex flex-col" data-panel="history" style="min-height: 70vh;">
        <div class="flex items-center gap-2 px-4 py-2 border-b oc-border shrink-0" style="background: rgba(148,163,184,.08);">
            <span class="text-sm font-semibold oc-heading">浏览历史</span>
        </div>
        <iframe class="w-full flex-1 border-0 min-h-[65vh]" title="浏览历史" data-embed-src="{{ route('history.index') }}{{ $embedQs }}"></iframe>
    </div>
    <div class="oc-dash-panel oc-surface overflow-hidden hidden flex flex-col" data-panel="comments" style="min-height: 70vh;">
        <div class="flex items-center gap-2 px-4 py-2 border-b oc-border shrink-0" style="background: rgba(148,163,184,.08);">
            <span class="text-sm font-semibold oc-heading">我的评论</span>
        </div>
        <iframe class="w-full flex-1 border-0 min-h-[65vh]" title="我的评论" data-embed-src="{{ route('dashboard.comments') }}{{ $embedQs }}"></iframe>
    </div>
    <div class="oc-dash-panel oc-surface overflow-hidden hidden flex flex-col" data-panel="orders" style="min-height: 70vh;">
        <div class="flex items-center gap-2 px-4 py-2 border-b oc-border shrink-0" style="background: rgba(148,163,184,.08);">
            <span class="text-sm font-semibold oc-heading">我的订单</span>
        </div>
        <iframe class="w-full flex-1 border-0 min-h-[65vh]" title="我的订单" data-embed-src="{{ route('dashboard.orders') }}{{ $embedQs }}"></iframe>
    </div>
    <div class="oc-dash-panel oc-surface overflow-hidden hidden flex flex-col" data-panel="points" style="min-height: 70vh;">
        <div class="flex items-center gap-2 px-4 py-2 border-b oc-border shrink-0" style="background: rgba(148,163,184,.08);">
            <span class="text-sm font-semibold oc-heading">积分与充值</span>
        </div>
        <iframe class="w-full flex-1 border-0 min-h-[65vh]" title="积分与充值" data-embed-src="{{ route('dashboard.points') }}{{ $embedQs }}"></iframe>
    </div>
    @if ($u->role === 'svip' || $u->isAdmin())
        <div class="oc-dash-panel oc-surface overflow-hidden hidden flex flex-col" data-panel="svip" style="min-height: 70vh;">
            <div class="flex items-center gap-2 px-4 py-2 border-b oc-border shrink-0" style="background: rgba(148,163,184,.08);">
                <span class="text-sm font-semibold oc-heading">SVIP 定制</span>
            </div>
            <iframe class="w-full flex-1 border-0 min-h-[65vh]" title="SVIP 定制" data-embed-src="{{ route('svip-subscriptions.index') }}{{ $embedQs }}"></iframe>
        </div>
    @endif
        </section>
    </div>

@endsection

@push('scripts')
    <script>
        (function () {
            const np = document.getElementById('new-pw');
            const st = document.getElementById('dash-pw-strength');
            if (np && st) {
                np.addEventListener('input', function () {
                    var n = np.value.length;
                    if (!n) {
                        st.textContent = '';
                        return;
                    }
                    if (n < 6) st.textContent = '强度：弱（至少 6 位）';
                    else if (n <= 10) st.textContent = '强度：中';
                    else st.textContent = '强度：强';
                });
            }
            const sendBtn = document.getElementById('dash-email-send');
            const emailEl = document.getElementById('dash-new-email');
            if (sendBtn && emailEl) {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                let cooldown = 0;
                let timer = null;
                sendBtn.addEventListener('click', async function () {
                    const email = emailEl.value.trim();
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                        alert('请先填写正确的新邮箱');
                        return;
                    }
                    if (cooldown > 0) return;
                    sendBtn.disabled = true;
                    try {
                        const res = await fetch('{{ url('/profile/email/send-code') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({ email }),
                        });
                        const data = await res.json().catch(function () {
                            return {};
                        });
                        if (!res.ok) {
                            alert(data.errors?.email?.[0] || data.message || '发送失败');
                            sendBtn.disabled = false;
                            return;
                        }
                        cooldown = 60;
                        sendBtn.textContent = '已发送（60s）';
                        timer = setInterval(function () {
                            cooldown--;
                            if (cooldown <= 0) {
                                clearInterval(timer);
                                sendBtn.textContent = '获取验证码';
                                sendBtn.disabled = false;
                            } else {
                                sendBtn.textContent = '已发送（' + cooldown + 's）';
                            }
                        }, 1000);
                    } catch (e) {
                        alert('网络错误');
                        sendBtn.disabled = false;
                    }
                });
            }
        })();
    </script>
    <script>
        (function () {
            const shell = document.getElementById('oc-dashboard-shell');
            const left = document.getElementById('oc-dash-left');
            const resizer = document.getElementById('oc-dash-resizer');
            if (!shell || !left || !resizer) return;

            // Tabs: 左侧菜单切换右侧内容
            const tabs = Array.from(shell.querySelectorAll('.oc-dash-tab'));
            const panels = Array.from(shell.querySelectorAll('.oc-dash-panel'));
            const setActive = (name) => {
                tabs.forEach((t) => {
                    const on = t.getAttribute('data-tab') === name;
                    t.classList.toggle('oc-link', !on);
                    t.classList.toggle('oc-heading', on);
                    t.style.background = on ? 'rgba(148,163,184,.12)' : 'transparent';
                });
                panels.forEach((p) => {
                    const on = p.getAttribute('data-panel') === name;
                    p.classList.toggle('hidden', !on);
                    if (on) {
                        const iframe = p.querySelector('iframe[data-embed-src]');
                        if (iframe) {
                            const src = iframe.getAttribute('data-embed-src');
                            if (src && !iframe.getAttribute('src')) {
                                iframe.setAttribute('src', src);
                            }
                        }
                    }
                });
            };
            tabs.forEach((t) => t.addEventListener('click', () => setActive(t.getAttribute('data-tab'))));
            const params = new URLSearchParams(window.location.search);
            const tabParam = params.get('tab');
            const allowed = ['profile', 'subscription', 'timeline', 'posts', 'favorites', 'history', 'comments', 'orders', 'points', 'svip'];
            let initialTab = 'profile';
            if (tabParam && allowed.includes(tabParam)) {
                initialTab = tabParam;
            } else if (window.location.hash === '#timeline') {
                initialTab = 'timeline';
            }
            setActive(initialTab);

            // Resizer: 调整左侧宽度（桌面端）
            let dragging = false;
            let startX = 0;
            let startW = 0;
            const clamp = (n, a, b) => Math.max(a, Math.min(b, n));
            const onMove = (e) => {
                if (!dragging) return;
                const dx = (e.clientX || 0) - startX;
                const next = clamp(startW + dx, 240, 520);
                shell.style.setProperty('--ocDashLeft', `${next}px`);
            };
            const stop = () => {
                if (!dragging) return;
                dragging = false;
                document.body.style.cursor = '';
                document.body.style.userSelect = '';
            };
            resizer.addEventListener('mousedown', (e) => {
                dragging = true;
                startX = e.clientX || 0;
                startW = left.getBoundingClientRect().width || 320;
                document.body.style.cursor = 'col-resize';
                document.body.style.userSelect = 'none';
                e.preventDefault();
            });
            window.addEventListener('mousemove', onMove);
            window.addEventListener('mouseup', stop);
        })();
    </script>
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
    @if ($u->canAccessVipExclusiveContent())
        <script>
            (function () {
                const form = document.getElementById('email-sub-form');
                if (!form) return;
                const input = document.getElementById('email-sub-input');
                const msg = document.getElementById('email-sub-msg');
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                form.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    const email = @json($u->email);
                    if (!email) return;
                    const checked = Array.from(form.querySelectorAll('.email-sub-topic:checked')).map((x) => x.value);
                    if (!checked.length) {
                        msg.textContent = '';
                        if (typeof window.ocToast === 'function') {
                            window.ocToast('请至少选择一个订阅内容', 'warning');
                        } else {
                            msg.textContent = '请至少选择一个订阅内容';
                            msg.style.color = '#b91c1c';
                        }
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
                                subscribed_to: checked,
                                topic_schedule: schedule,
                            }),
                        });
                        const data = await res.json().catch(() => ({}));
                        msg.textContent = '';
                        msg.style.color = '';
                        if (typeof window.ocToast === 'function') {
                            if (res.ok) {
                                window.ocToast('订阅成功，后续可在后台邮件订阅管理查看。', 'success');
                            } else {
                                window.ocToast(data.message || '订阅失败，请稍后重试', 'error');
                            }
                        } else {
                            msg.textContent = res.ok ? '订阅成功，后续可在后台邮件订阅管理查看。' : (data.message || '订阅失败，请稍后重试');
                            msg.style.color = res.ok ? '#166534' : '#b91c1c';
                        }
                    } catch (err) {
                        msg.textContent = '';
                        if (typeof window.ocToast === 'function') {
                            window.ocToast('网络异常，请稍后重试', 'error');
                        } else {
                            msg.textContent = '网络异常，请稍后重试';
                            msg.style.color = '#b91c1c';
                        }
                    }
                });
            })();
        </script>
    @endif
    <script>
        (function () {
            const ul = document.getElementById('dash-guess-list');
            if (!ul) return;
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch('/api/public/guess/user-posts', {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token || '' },
                credentials: 'same-origin',
            })
                .then((r) => r.json())
                .then((data) => {
                    const items = data.items || [];
                    if (!items.length) {
                        ul.innerHTML = '<li class="oc-muted">暂无可推荐投稿</li>';
                        return;
                    }
                    ul.innerHTML = items
                        .map(
                            (it) =>
                                '<li class="leading-snug"><a class="oc-link font-medium" style="text-decoration:none;" href="' +
                                it.url +
                                '">' +
                                (it.title || '').replace(/</g, '&lt;') +
                                '</a></li>'
                        )
                        .join('');
                })
                .catch(function () {
                    ul.innerHTML = '<li class="text-red-600">加载失败</li>';
                });
        })();
    </script>
@endpush
