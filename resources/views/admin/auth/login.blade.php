@extends('layouts.admin-guest')

@section('title', '管理员登录')

@section('content')
    <div class="admin-auth-page">
        <div class="auth-card">
            <div class="auth-title">后台登录</div>
            <div class="auth-sub">仅限管理员账号</div>

            @if (session('success'))
                <div class="auth-alert auth-alert--ok">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="auth-alert auth-alert--err">请检查输入信息</div>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}" novalidate>
                @csrf

                <div class="auth-field">
                    <label class="auth-label" for="email">邮箱</label>
                    <input class="auth-input" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="admin@example.com" required>
                    @error('email')
                        <div class="auth-err">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-field">
                    <label class="auth-label" for="password">密码</label>
                    <input class="auth-input" id="password" name="password" type="password" placeholder="请输入密码" required>
                    @error('password')
                        <div class="auth-err">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-row-between">
                    <label class="auth-remember">
                        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        记住我
                    </label>
                </div>

                <button class="btn btn-primary auth-btn-wide" type="submit">登录</button>

                <div class="auth-footer">
                    <a class="auth-link" href="{{ config('app.url') }}">返回前台</a>
                </div>
            </form>
        </div>
    </div>
@endsection
