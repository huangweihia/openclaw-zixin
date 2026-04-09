@extends('layouts.admin')

@section('title', '内容管理')

@section('content')
    <h1 class="admin-page-title">内容管理</h1>
    <p class="admin-page-lead">文章、项目、分类的增删改查将在此接入（对应文档「内容管理模块」）。</p>
    <p class="admin-muted">当前前台内容由迁移与 Seeder 维护；后续可接 CRUD 与富文本。</p>
    <p><a href="{{ route('articles.index') }}" class="btn btn-secondary">前台文章列表</a>
        <a href="{{ route('projects.index') }}" class="btn btn-secondary">前台项目列表</a></p>
@endsection
