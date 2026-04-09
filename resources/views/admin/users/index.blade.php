@extends('layouts.admin')

@section('title', '用户管理')

@section('content')
    <h1 class="admin-page-title">用户管理</h1>
    <p class="admin-page-lead">分页列表，支持按昵称或邮箱搜索。</p>

    <form method="get" action="{{ route('admin.users.index') }}" class="admin-toolbar">
        <input type="search" name="q" value="{{ $q }}" placeholder="昵称或邮箱" class="admin-input" style="max-width:280px;">
        <button type="submit" class="btn btn-primary">搜索</button>
        @if ($q !== '')
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">清除</a>
        @endif
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>昵称</th>
                    <th>邮箱</th>
                    <th>角色</th>
                    <th>状态</th>
                    <th>VIP 到期</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr @if($user->is_banned) class="is-muted" @endif>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->role }}</td>
                        <td>
                            @if ($user->is_banned)
                                <span class="admin-badge admin-badge--danger">已禁用</span>
                            @else
                                <span class="admin-badge admin-badge--ok">正常</span>
                            @endif
                        </td>
                        <td>{{ $user->subscription_ends_at?->format('Y-m-d') ?? '—' }}</td>
                        <td>
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-secondary btn--sm">编辑</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">
        {{ $users->links() }}
    </div>
@endsection
