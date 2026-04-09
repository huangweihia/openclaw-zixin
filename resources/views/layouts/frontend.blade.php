<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'OpenClaw 智信')</title>
    
    <!-- 预加载关键资源 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Vite 资源 -->
    @vite(['resources/css/app.css', 'resources/js/frontend/main.js'])
    
    <!-- 皮肤样式 -->
    <style>
        /* 防止 FOUC - 页面加载时隐藏内容直到皮肤应用 */
        body {
            opacity: 1;
            transition: opacity 0.1s ease;
        }
    </style>
    
    <!-- 皮肤初始化脚本（防止闪烁） -->
    <script>
        (function() {
            const savedSkin = localStorage.getItem('preferred_skin');
            if (savedSkin) {
                document.documentElement.setAttribute('data-skin', savedSkin);
            }
        })();
    </script>
    
    @stack('styles')
</head>
<body>
    <!-- Vue 应用挂载点 -->
    <div id="app"></div>
    
    <!-- 全局配置 -->
    <script>
        // CSRF Token for Axios
        window.axiosDefaults = window.axiosDefaults || {};
        window.axiosDefaults.headers = window.axiosDefaults.headers || {};
        window.axiosDefaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        window.axiosDefaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        window.axiosDefaults.headers.common['Accept'] = 'application/json';
    </script>
    
    @stack('scripts')
</body>
</html>
