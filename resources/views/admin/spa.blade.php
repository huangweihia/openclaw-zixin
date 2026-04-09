<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>管理后台 — OpenClaw 智信</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    {{-- Toast / v-loading 使用 Tailwind 工具类；关闭 preflight 以免覆盖管理端布局 --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { corePlugins: { preflight: false } };
    </script>
    <script>
        window.__ADMIN_ROUTER_BASE__ = @json($routerBase);
    </script>
    <style>
        /* 线上兜底：即使旧构建缓存未更新，后台按钮文字也保持可见 */
        #admin-app .btn,
        #admin-app button.btn {
            color: #0f172a;
        }
        #admin-app .btn.primary,
        #admin-app .btn.btn--pri,
        #admin-app button.btn.primary,
        #admin-app button.btn.btn--pri {
            color: #fff !important;
        }
    </style>
    @vite(['resources/js/admin/main.js'])
</head>
<body>
    <div id="admin-app"></div>
</body>
</html>
