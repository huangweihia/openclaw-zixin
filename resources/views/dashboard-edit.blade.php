@extends('layouts.site')

@section('title', '编辑资料 — OpenClaw 智信')

@section('content')
    @php
        $u = auth()->user();
    @endphp

    <p class="mb-4">
        <a href="{{ route('dashboard') }}" class="oc-link text-sm font-medium" style="text-decoration: none;">← 返回个人中心</a>
    </p>
    <h1 class="text-2xl md:text-3xl font-bold mb-8 oc-heading">编辑资料</h1>

    <div class="max-w-2xl space-y-8">
        <div class="oc-surface p-6">
            <h3 class="text-lg font-bold oc-heading mb-4">头像</h3>
            <div class="flex flex-wrap items-center gap-6">
                @if ($u->avatar)
                    <img src="{{ $u->avatar }}" alt="" class="w-24 h-24 rounded-full object-cover border-2" style="border-color: rgba(148,163,184,.35);" id="dash-avatar-preview" />
                @else
                    <div class="w-24 h-24 rounded-full flex items-center justify-center text-2xl font-bold text-white" style="background: var(--gradient-primary);" id="dash-avatar-preview">
                        {{ mb_substr($u->name, 0, 1) }}
                    </div>
                @endif
                <form method="post" action="{{ route('profile.avatar') }}" enctype="multipart/form-data">
                    @csrf
                    <label class="btn btn-secondary text-sm cursor-pointer inline-block">
                        更换头像
                        <input type="file" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden" onchange="this.form.requestSubmit()" />
                    </label>
                </form>
            </div>
        </div>

        <div class="oc-surface p-6">
            <h3 class="text-lg font-bold oc-heading mb-4">修改昵称</h3>
            <form method="post" action="{{ route('profile.name') }}">
                @csrf
                <div class="oc-field">
                    <label class="oc-label" for="dash-name">昵称</label>
                    <input type="text" name="name" id="dash-name" value="{{ old('name', $u->name) }}" required minlength="2" maxlength="50" class="oc-input" />
                </div>
                <button type="submit" class="btn btn-primary text-sm">保存</button>
            </form>
        </div>

        <div class="oc-surface p-6">
            <h3 class="text-lg font-bold oc-heading mb-4">个人简介</h3>
            <form method="post" action="{{ route('profile.bio') }}">
                @csrf
                <div class="oc-field">
                    <label class="oc-label" for="dash-bio">签名（选填，最多 500 字）</label>
                    <textarea name="bio" id="dash-bio" rows="3" maxlength="500" class="oc-input" placeholder="一句话介绍自己">{{ old('bio', $u->bio) }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary text-sm">保存简介</button>
            </form>
        </div>

        <div class="oc-surface p-6">
            <h3 class="text-lg font-bold oc-heading mb-4">隐私与足迹</h3>
            <p class="text-xs oc-muted mb-4">开启后，系统将<strong>不再写入</strong>浏览历史（不影响已有记录）。</p>
            @if (session('privacy_saved'))
                <div class="oc-flash oc-flash--success mb-4 text-sm" role="status">隐私设置已更新</div>
            @endif
            <form method="post" action="{{ route('profile.privacy') }}">
                @csrf
                <input type="hidden" name="privacy_mode" value="0" />
                <label class="flex items-center gap-3 cursor-pointer text-sm oc-heading">
                    <input type="checkbox" name="privacy_mode" value="1" class="rounded" @checked(old('privacy_mode', (bool) ($u->privacy_mode ?? false))) />
                    隐私模式（不记录浏览历史）
                </label>
                <button type="submit" class="btn btn-primary text-sm mt-4">保存隐私设置</button>
            </form>
        </div>

        <div class="oc-surface p-6">
            <h3 class="text-lg font-bold oc-heading mb-4">企业微信</h3>
            <p class="text-xs oc-muted mb-4">填写企业微信通讯录中的 <strong>userid</strong>，便于后续通过应用消息推送通知；或在企微内打开本站并点击下方按钮完成 OAuth 绑定（需在 .env 配置 WECOM_* 且企微后台授权回调 URL 与 <code class="text-xs">/wecom/oauth/callback</code> 一致）。应用消息发送见 <code class="text-xs">App\Services\WeCom\WeComMessageService</code>。</p>
            @if (session('wecom_oauth_error'))
                <div class="oc-flash oc-flash--error mb-4 text-sm" role="alert">{{ session('wecom_oauth_error') }}</div>
            @endif
            @if (session('wecom_saved'))
                <div class="oc-flash oc-flash--success mb-4 text-sm" role="status">企业微信信息已保存</div>
            @endif
            <p class="mb-4">
                <a href="{{ route('wecom.oauth.start') }}" class="btn btn-secondary text-sm">企业微信 OAuth 绑定</a>
            </p>
            <form method="post" action="{{ route('profile.wecom') }}">
                @csrf
                <div class="oc-field">
                    <label class="oc-label" for="dash-wecom-id">企业微信 userid</label>
                    <input type="text" name="enterprise_wechat_id" id="dash-wecom-id" value="{{ old('enterprise_wechat_id', $u->enterprise_wechat_id) }}" maxlength="128" class="oc-input" placeholder="选填" autocomplete="off" />
                </div>
                <button type="submit" class="btn btn-primary text-sm">保存</button>
            </form>
        </div>

        <div class="oc-surface p-6">
            <h3 class="text-lg font-bold oc-heading mb-4">修改登录邮箱</h3>
            <p class="text-xs oc-muted mb-4">验证码将发到<strong>新邮箱</strong>；需填写当前登录密码以确认身份。</p>
            @if ($errors->any() && old('_email_form'))
                <div class="oc-flash oc-flash--error mb-4" role="alert">
                    <ul class="text-sm list-disc list-inside space-y-1 m-0">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="post" action="{{ route('profile.email') }}" id="dash-email-form">
                @csrf
                <input type="hidden" name="_email_form" value="1" />
                <div class="oc-field">
                    <label class="oc-label" for="dash-new-email">新邮箱</label>
                    <div class="flex gap-2 flex-wrap">
                        <input type="email" name="email" id="dash-new-email" value="{{ old('email') }}" required autocomplete="email" class="oc-input flex-1 min-w-[200px]" />
                        <button type="button" id="dash-email-send" class="btn btn-secondary text-sm whitespace-nowrap">获取验证码</button>
                    </div>
                </div>
                <div class="oc-field">
                    <label class="oc-label" for="dash-email-code">新邮箱验证码</label>
                    <input type="text" name="verification_code" id="dash-email-code" value="{{ old('verification_code') }}" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" required class="oc-input" placeholder="6 位数字" />
                </div>
                <div class="oc-field">
                    <label class="oc-label" for="dash-email-pw">当前密码</label>
                    <input type="password" name="current_password" id="dash-email-pw" required class="oc-input" autocomplete="current-password" />
                </div>
                <button type="submit" class="btn btn-primary text-sm">确认更换邮箱</button>
            </form>
        </div>

        <div class="oc-surface p-6">
            <h3 class="text-lg font-bold oc-heading mb-4">修改密码</h3>
            <form method="post" action="{{ route('profile.password') }}">
                @csrf
                <div class="oc-field">
                    <label class="oc-label" for="cur-pw">当前密码</label>
                    <input type="password" name="current_password" id="cur-pw" required class="oc-input" autocomplete="current-password" />
                </div>
                <div class="oc-field">
                    <label class="oc-label" for="new-pw">新密码</label>
                    <input type="password" name="password" id="new-pw" required minlength="6" class="oc-input" autocomplete="new-password" />
                    <p class="text-xs oc-muted mt-1" id="dash-pw-strength"></p>
                </div>
                <div class="oc-field">
                    <label class="oc-label" for="new-pw2">确认新密码</label>
                    <input type="password" name="password_confirmation" id="new-pw2" required class="oc-input" autocomplete="new-password" />
                </div>
                <button type="submit" class="btn btn-primary text-sm">更新密码</button>
            </form>
        </div>
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
@endpush
