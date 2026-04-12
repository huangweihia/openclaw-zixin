@extends('layouts.site')

@section('title', '登录 — OpenClaw 智信')

@section('content')
    <div class="max-w-md mx-auto oc-surface p-8">
        <h1 class="oc-auth-page-title text-center mb-2">登录 OpenClaw 智信</h1>
        <p class="text-sm mb-8 oc-muted text-center">使用邮箱与密码登录</p>

        @if ($errors->any())
            <div class="oc-flash oc-flash--error mb-4" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('post_login_redirect'))
            <p class="text-sm oc-muted text-center mb-6 m-0" role="status">登录成功，正在进入站点…</p>
        @endif

        <form method="post" action="{{ route('login') }}" id="login-form" @if (session('post_login_redirect')) class="hidden" aria-hidden="true" @endif>
            @csrf
            <input type="hidden" name="return" value="{{ old('return', $returnTo ?? '') }}" />

            <div class="oc-field">
                <label class="oc-label" for="email">邮箱</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                    autocomplete="email"
                    class="oc-input @error('email') oc-input--error @enderror" />
                @error('email')
                    <p class="auth-err">{{ $message }}</p>
                @enderror
                <p id="email-err" class="auth-err hidden"></p>
            </div>

            <div class="oc-field">
                <label class="oc-label" for="password">密码</label>
                <input type="password" name="password" id="password" required
                    class="oc-input @error('password') oc-input--error @enderror" autocomplete="current-password" />
            </div>

            <div class="oc-form-row mb-6">
                <label class="inline-flex items-center gap-2 text-sm cursor-pointer oc-muted">
                    <input type="checkbox" name="remember" value="1" class="oc-checkbox"
                        {{ old('remember') ? 'checked' : '' }} />
                    记住我
                </label>
                <button type="button" id="forgot-btn" class="text-sm oc-link">
                    忘记密码？
                </button>
            </div>

            <button type="submit" id="login-submit" class="btn btn-primary w-full">登录</button>
        </form>

        <p class="text-center text-sm mt-6 oc-muted">
            还没有账号？
            <a href="{{ route('register') }}" class="oc-link font-semibold" style="text-decoration: none;">免费注册</a>
        </p>
    </div>

    <div id="forgot-modal" class="oc-modal-overlay hidden" role="dialog" aria-modal="true" aria-labelledby="forgot-title">
        <div class="oc-modal">
            <h2 id="forgot-title" class="text-lg font-bold mb-2">找回密码</h2>
            <p class="text-sm mb-4 oc-muted">请联系客服协助重置密码。</p>
            <button type="button" id="forgot-close" class="btn btn-primary w-full">知道了</button>
        </div>
    </div>
@endsection

@push('scripts')
    @if (session('post_login_redirect'))
        <script>
            (function () {
                var u = @json(session('post_login_redirect'));
                setTimeout(function () {
                    window.location.replace(u);
                }, 900);
            })();
        </script>
    @endif
    <script>
        (function () {
            const emailEl = document.getElementById('email');
            const err = document.getElementById('email-err');
            function validEmail(v) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(v).trim());
            }
            function showErr(show) {
                if (!err) return;
                if (show) {
                    err.textContent = '请输入正确的邮箱';
                    err.classList.remove('hidden');
                    emailEl.classList.add('oc-input--error');
                } else {
                    err.classList.add('hidden');
                    emailEl.classList.remove('oc-input--error');
                }
            }
            emailEl.addEventListener('input', function () {
                const v = emailEl.value.trim();
                if (v && !validEmail(v)) showErr(true);
                else showErr(false);
            });
            emailEl.addEventListener('blur', function () {
                const v = emailEl.value.trim();
                if (v && !validEmail(v)) showErr(true);
            });

            document.getElementById('login-form').addEventListener('submit', function (e) {
                if (!validEmail(emailEl.value.trim())) {
                    e.preventDefault();
                    showErr(true);
                    return;
                }
                const btn = document.getElementById('login-submit');
                btn.disabled = true;
                btn.textContent = '登录中...';
            });

            const modal = document.getElementById('forgot-modal');
            document.getElementById('forgot-btn').addEventListener('click', function () {
                modal.classList.remove('hidden');
            });
            document.getElementById('forgot-close').addEventListener('click', function () {
                modal.classList.add('hidden');
            });
            modal.addEventListener('click', function (e) {
                if (e.target === modal) modal.classList.add('hidden');
            });
        })();
    </script>
@endpush
