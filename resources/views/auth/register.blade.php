@extends('layouts.site')

@section('title', '注册 — OpenClaw 智信')

@section('content')
    <div class="max-w-md mx-auto oc-surface p-8">
        <h1 class="oc-auth-page-title text-center mb-2">免费注册 OpenClaw 智信</h1>
        <p class="text-sm mb-8 oc-muted text-center">使用邮箱验证码注册，安全便捷</p>

        @if ($errors->any())
            <div class="oc-flash oc-flash--error mb-4" role="alert">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="{{ route('register') }}" id="register-form" novalidate>
            @csrf
            @if (!empty($trial))
                <input type="hidden" name="trial" value="{{ e($trial) }}">
            @endif

            <div class="oc-field">
                <label class="oc-label" for="email">邮箱</label>
                <div class="flex gap-2">
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                        autocomplete="email"
                        class="oc-input flex-1 @error('email') oc-input--error @enderror"
                        placeholder="you@example.com" />
                    <button type="button" id="btn-send-code" class="btn btn-secondary whitespace-nowrap text-sm px-3">
                        获取验证码
                    </button>
                </div>
                @error('email')
                    <p class="auth-err">{{ $message }}</p>
                @enderror
                <p id="email-client-err" class="auth-err hidden"></p>
            </div>

            <div class="oc-field">
                <label class="oc-label" for="verification_code">邮箱验证码</label>
                <input type="text" name="verification_code" id="verification_code" value="{{ old('verification_code') }}"
                    inputmode="numeric" maxlength="6" pattern="[0-9]{6}"
                    class="oc-input @error('verification_code') oc-input--error @enderror" placeholder="6 位数字" />
                @error('verification_code')
                    <p class="auth-err">{{ $message }}</p>
                @enderror
            </div>

            <div class="oc-field">
                <label class="oc-label" for="password">密码</label>
                <input type="password" name="password" id="password" required minlength="6"
                    class="oc-input @error('password') oc-input--error @enderror" autocomplete="new-password" />
                <p id="pwd-strength" class="text-xs mt-1 oc-muted"></p>
                @error('password')
                    <p class="auth-err">{{ $message }}</p>
                @enderror
            </div>

            <div class="oc-field">
                <label class="oc-label" for="password_confirmation">确认密码</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                    class="oc-input @error('password_confirmation') oc-input--error @enderror"
                    autocomplete="new-password" />
                <p id="pwd-match-err" class="auth-err hidden"></p>
            </div>

            <div class="oc-field">
                <label class="oc-label" for="name">昵称（可选）</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                    class="oc-input @error('name') oc-input--error @enderror" maxlength="50" placeholder="2–50 个字符" />
                @error('name')
                    <p class="auth-err">{{ $message }}</p>
                @enderror
            </div>

            <p class="text-xs mb-4 oc-muted">
                点击注册即表示同意
                <a href="{{ route('terms') }}" class="oc-link">服务条款</a>
                与
                <a href="{{ route('privacy') }}" class="oc-link">隐私政策</a>
            </p>

            <button type="submit" id="btn-submit" class="btn btn-primary w-full">同意协议并注册</button>
        </form>

        <p class="text-center text-sm mt-6 oc-muted">
            已有账号？
            <a href="{{ route('login') }}" class="oc-link font-semibold" style="text-decoration: none;">立即登录</a>
        </p>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const emailEl = document.getElementById('email');
            const sendBtn = document.getElementById('btn-send-code');
            const pwdEl = document.getElementById('password');
            const pwd2El = document.getElementById('password_confirmation');
            const strengthEl = document.getElementById('pwd-strength');
            const matchErr = document.getElementById('pwd-match-err');
            const emailClientErr = document.getElementById('email-client-err');
            const form = document.getElementById('register-form');

            function validEmail(v) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(v).trim());
            }

            function setEmailError(show, msg) {
                if (!emailClientErr) return;
                if (show) {
                    emailClientErr.textContent = msg;
                    emailClientErr.classList.remove('hidden');
                    emailEl.classList.add('oc-input--error');
                } else {
                    emailClientErr.classList.add('hidden');
                    emailEl.classList.remove('oc-input--error');
                }
            }

            emailEl.addEventListener('input', function () {
                const v = emailEl.value.trim();
                if (v && !validEmail(v)) setEmailError(true, '请输入正确的邮箱');
                else setEmailError(false);
            });
            emailEl.addEventListener('blur', function () {
                const v = emailEl.value.trim();
                if (v && !validEmail(v)) setEmailError(true, '请输入正确的邮箱');
            });

            pwdEl.addEventListener('input', function () {
                const len = pwdEl.value.length;
                if (len === 0) {
                    strengthEl.textContent = '';
                    return;
                }
                if (len < 6) strengthEl.textContent = '密码强度：弱（至少 6 位）';
                else if (len <= 10) strengthEl.textContent = '密码强度：中';
                else strengthEl.textContent = '密码强度：强';
            });
            pwdEl.addEventListener('blur', function () {
                if (pwdEl.value.length > 0 && pwdEl.value.length < 6) {
                    strengthEl.textContent = '至少 6 位';
                }
            });

            function syncPwdMatch() {
                const a = pwdEl.value;
                const b = pwd2El.value;
                if (b.length === 0) {
                    matchErr.classList.add('hidden');
                    pwd2El.classList.remove('oc-input--error');
                    return;
                }
                if (a !== b) {
                    matchErr.textContent = '密码不一致';
                    matchErr.classList.remove('hidden');
                    pwd2El.classList.add('oc-input--error');
                } else {
                    matchErr.classList.add('hidden');
                    pwd2El.classList.remove('oc-input--error');
                }
            }
            pwd2El.addEventListener('input', syncPwdMatch);
            pwdEl.addEventListener('input', syncPwdMatch);

            let cooldown = 0;
            let timer = null;

            sendBtn.addEventListener('click', async function () {
                const email = emailEl.value.trim();
                if (!validEmail(email)) {
                    setEmailError(true, '请输入正确的邮箱');
                    return;
                }
                if (cooldown > 0) return;
                sendBtn.disabled = true;
                try {
                    const res = await fetch('{{ url('/register/send-code') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ email }),
                        credentials: 'same-origin',
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        const msg =
                            data.errors?.email?.[0] ||
                            data.message ||
                            (res.status === 422 ? '发送失败' : '请求失败');
                        alert(msg);
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
                    alert('网络错误，请稍后重试');
                    sendBtn.disabled = false;
                }
            });

            form.addEventListener('submit', function (e) {
                const email = emailEl.value.trim();
                if (!validEmail(email)) {
                    e.preventDefault();
                    setEmailError(true, '请输入正确的邮箱');
                    return;
                }
                if (pwdEl.value.length < 6) {
                    e.preventDefault();
                    alert('密码至少 6 位');
                    return;
                }
                if (pwdEl.value !== pwd2El.value) {
                    e.preventDefault();
                    syncPwdMatch();
                    return;
                }
                const btn = document.getElementById('btn-submit');
                btn.disabled = true;
                btn.textContent = '注册中...';
            });
        })();
    </script>
@endpush
