@extends('layouts.admin')

@section('title', '编辑用户')

@section('content')
    <h1 class="admin-page-title">编辑用户</h1>
    <p class="admin-page-lead">{{ $user->email }}</p>

    <p class="admin-back">
        <a href="{{ route('admin.users.index') }}">← 返回列表</a>
    </p>

    <section class="admin-panel">
        <h2 class="admin-panel__title">基本资料</h2>
        <form method="post" action="{{ route('admin.users.update', $user) }}" class="admin-form">
            @csrf
            @method('PUT')
            <div class="admin-form__row">
                <label class="oc-label" for="name">昵称</label>
                <input id="name" name="name" type="text" class="admin-input" value="{{ old('name', $user->name) }}" required maxlength="255">
            </div>
            <div class="admin-form__row">
                <label class="oc-label" for="email">邮箱</label>
                <input id="email" name="email" type="email" class="admin-input" value="{{ old('email', $user->email) }}" required maxlength="255">
            </div>
            <div class="admin-form__row">
                <label class="oc-label" for="role">角色</label>
                <select id="role" name="role" class="admin-input">
                    @foreach (['user' => '普通用户', 'vip' => 'VIP', 'svip' => 'SVIP', 'admin' => '管理员'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('role', $user->role) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </section>

    <section class="admin-panel">
        <h2 class="admin-panel__title">发放 VIP（后台赠送）</h2>
        <p class="admin-muted">将用户角色设为 VIP，并写入一条订阅记录；终身方案忽略「天数」但仍需填写 ≥1。</p>
        <form method="post" action="{{ route('admin.users.vip', $user) }}" class="admin-form">
            @csrf
            @method('PUT')
            <div class="admin-form__row admin-form__row--inline">
                <div>
                    <label class="oc-label" for="plan">套餐</label>
                    <select id="plan" name="plan" class="admin-input">
                        <option value="monthly">月付（按天数）</option>
                        <option value="yearly">年付（按天数）</option>
                        <option value="lifetime">终身</option>
                    </select>
                </div>
                <div>
                    <label class="oc-label" for="days">天数</label>
                    <input id="days" name="days" type="number" class="admin-input" value="{{ old('days', 30) }}" min="1" max="3650" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">确认发放</button>
        </form>
    </section>

    <section class="admin-panel">
        <h2 class="admin-panel__title">账号状态</h2>
        @if ($user->id === auth()->id())
            <p class="admin-muted">不能禁用当前登录账号。</p>
        @elseif ($user->role === 'admin')
            <p class="admin-muted">管理员账号请先在上方改为非管理员角色后再禁用。</p>
        @elseif ($user->is_banned)
            <form method="post" action="{{ route('admin.users.enable', $user) }}" class="admin-inline-form">
                @csrf
                @method('PUT')
                <button type="submit" class="btn btn-primary">解除禁用</button>
            </form>
        @else
            <form method="post" action="{{ route('admin.users.disable', $user) }}" class="admin-inline-form" onsubmit="return confirm('确定禁用该用户？其将无法登录。');">
                @csrf
                @method('PUT')
                <button type="submit" class="btn btn-secondary">禁用账号</button>
            </form>
        @endif
    </section>
@endsection
