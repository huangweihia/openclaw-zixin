@extends('layouts.site')

@section('title', '新建投稿 — OpenClaw 智信')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
@endpush

@section('content')
    <div class="max-w-2xl mx-auto">
        <p class="mb-4">
            <a href="{{ route('user-posts.index') }}" class="oc-link text-sm font-medium" style="text-decoration: none;">← 返回我的发布</a>
        </p>
        <h1 class="text-2xl font-bold oc-heading mb-2">新建投稿</h1>

        <form id="user-post-create-form" method="post" action="{{ route('user-posts.store') }}" class="oc-surface p-6 space-y-4">
            @csrf
            <div class="oc-field">
                <label class="oc-label" for="up-type">类型</label>
                <select name="type" id="up-type" class="oc-input" required>
                    @foreach ([
                        'case' => '案例（同步展示在「案例」页社区投稿区）',
                        'tool' => '工具（同步展示在「工具」页社区投稿区）',
                        'experience' => '经验心得',
                        'resource' => '学习资源',
                        'question' => '提问讨论',
                    ] as $val => $lab)
                        <option value="{{ $val }}" @selected(old('type') === $val)>{{ $lab }}</option>
                    @endforeach
                </select>
            </div>
            <div class="oc-field">
                <label class="oc-label" for="up-title">标题</label>
                <input type="text" name="title" id="up-title" value="{{ old('title') }}" required minlength="4" maxlength="255" class="oc-input" />
            </div>
            <div class="oc-field">
                <label class="oc-label" for="up-content">正文（富文本）</label>
                <textarea name="content" id="up-content" rows="12" required minlength="20" maxlength="50000" class="oc-input text-sm" placeholder="不少于 20 字">{{ old('content') }}</textarea>
            </div>
            <div class="oc-field">
                <label class="oc-label" for="up-vis">可见范围</label>
                <select name="visibility" id="up-vis" class="oc-input" required>
                    <option value="public" @selected(old('visibility', 'public') === 'public')>公开</option>
                    <option value="vip" @selected(old('visibility') === 'vip')>仅 VIP 可读全文</option>
                    <option value="private" @selected(old('visibility') === 'private')>仅自己</option>
                </select>
            </div>
            @if ($errors->any())
                <div class="oc-flash oc-flash--error text-sm" role="alert">
                    <ul class="m-0 pl-4">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <button type="submit" class="btn btn-primary text-sm">提交审核</button>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof tinymce === 'undefined') return;
            var uploadUrl = @json(route('user.rich-upload.image'));
            tinymce.init({
                selector: '#up-content',
                height: 420,
                menubar: false,
                plugins: 'link lists code autoresize image',
                toolbar: 'undo redo | blocks | bold italic | bullist numlist | link image | code',
                branding: false,
                promotion: false,
                license_key: 'gpl',
                images_upload_credentials: true,
                automatic_uploads: true,
                images_upload_handler: function (blobInfo, progress) {
                    return new Promise(function (resolve, reject) {
                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', uploadUrl);
                        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                        xhr.upload.onprogress = function (e) {
                            progress(e.loaded / e.total * 100);
                        };
                        xhr.onload = function () {
                            if (xhr.status < 200 || xhr.status >= 300) {
                                reject('上传失败');
                                return;
                            }
                            var json = JSON.parse(xhr.responseText);
                            if (json && json.location) {
                                resolve(json.location);
                            } else {
                                reject('无效响应');
                            }
                        };
                        xhr.onerror = function () {
                            reject('网络错误');
                        };
                        var fd = new FormData();
                        fd.append('image', blobInfo.blob(), blobInfo.filename());
                        xhr.send(fd);
                    });
                },
                setup: function (editor) {
                    editor.on('change keyup', function () {
                        editor.save();
                    });
                },
            });
            var form = document.getElementById('user-post-create-form');
            if (form) {
                form.addEventListener('submit', function () {
                    if (tinymce.get('up-content')) {
                        tinymce.get('up-content').save();
                    }
                });
            }
        });
    </script>
@endpush
