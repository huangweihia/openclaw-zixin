<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        <meta name="user-id" content="{{ auth()->id() }}">
        <meta name="user-role" content="{{ auth()->user()->role }}">
    @endauth
    <title>@yield('title', ($ocSite['site_name'] ?? 'OpenClaw 智信'))</title>
    <script>
        try {
            var __sk = localStorage.getItem('preferred_skin');
            if (__sk) document.documentElement.setAttribute('data-skin', __sk);
        } catch (e) {}
    </script>
    <script>
        tailwind.config = { corePlugins: { preflight: false } };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/skins.css') }}">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @stack('head')
</head>
@php
    $ocSiteEmbed = request()->boolean('oc_embed');
@endphp
<body class="oc-page-bg min-h-screen flex flex-col {{ $ocSiteEmbed ? 'pt-0' : 'pt-16' }}">
    @unless ($ocSiteEmbed)
        @include('partials.navbar')
        @include('partials.announcement-marquee', ['placement' => 'top'])
        @include('partials.flash')
    @endunless

    <div class="flex-1 w-full {{ $ocSiteEmbed ? 'max-w-none px-3 py-3' : 'max-w-7xl mx-auto px-4 py-8' }}">
        <main class="min-w-0">
            @yield('content')
        </main>
    </div>

    @unless ($ocSiteEmbed)
        @include('partials.footer')
        @if (! \Illuminate\Support\Facades\View::hasSection('suppress_floating_promos'))
            @include('partials.floating-promos')
        @endif
    @endunless

    @vite(['resources/js/blade-skin-mount.js'])
    @include('partials.oc-toast')
    @include('partials.site-user-mini')
    <script>
        document.querySelectorAll('[data-oc-flash]').forEach(function (el) {
            setTimeout(function () {
                el.style.opacity = '0';
                setTimeout(function () {
                    el.remove();
                }, 280);
            }, 3000);
        });
    </script>
    @include('partials.pricing-countdown-init')
    @stack('scripts')
</body>
</html>
