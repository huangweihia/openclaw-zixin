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
    <style>
        /* 全块权限遮罩：马赛克感 = 强模糊 + 暗色叠层 + 细网格；整块可点，无穿透 */
        .oc-permission-mask-link {
            position: absolute;
            inset: 0;
            z-index: 30;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            text-decoration: none;
            border-radius: inherit;
            overflow: hidden;
            color: #f8fafc;
        }
        .oc-permission-mask-link__glass {
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(14px) saturate(0.7);
            -webkit-backdrop-filter: blur(14px) saturate(0.7);
        }
        .oc-permission-mask-link--svip .oc-permission-mask-link__glass {
            background: rgba(49, 46, 129, 0.52);
        }
        .oc-permission-mask-link__mosaic {
            position: absolute;
            inset: 0;
            border-radius: inherit;
            pointer-events: none;
            opacity: 0.55;
            background-image:
                repeating-linear-gradient(
                    0deg,
                    rgba(255, 255, 255, 0.04) 0px,
                    rgba(255, 255, 255, 0.04) 1px,
                    transparent 1px,
                    transparent 5px
                ),
                repeating-linear-gradient(
                    90deg,
                    rgba(0, 0, 0, 0.06) 0px,
                    rgba(0, 0, 0, 0.06) 1px,
                    transparent 1px,
                    transparent 5px
                );
            mix-blend-mode: overlay;
        }
        .oc-permission-mask-link__inner {
            position: relative;
            z-index: 2;
            max-width: 18rem;
            padding: 0.75rem 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.35rem;
        }
        .oc-permission-mask-link__emoji {
            font-size: 1.75rem;
            line-height: 1;
            filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.35));
        }
        .oc-permission-mask-link__title {
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        .oc-permission-mask-link__desc {
            font-size: 0.75rem;
            line-height: 1.45;
            opacity: 0.88;
        }
        .oc-permission-mask-link__cta {
            margin-top: 0.35rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.45rem 1rem;
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.35);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
        }
    </style>
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
