@php
    $ocSite = $ocSite ?? \App\Support\SiteViewComposer::branding();
@endphp
<footer class="oc-site-footer py-14 md:py-16 mt-auto">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid md:grid-cols-4 gap-8">
            <div>
                <h3 class="text-xl font-bold mb-3 oc-heading flex items-center gap-2 flex-wrap">
                    <img src="{{ $ocSite['site_logo_href'] ?? asset('favicon.svg') }}" alt="" width="24" height="24" class="shrink-0" decoding="async" />
                    <span>{{ $ocSite['site_name'] }}</span>
                </h3>
                <p class="oc-site-footer__muted text-sm leading-relaxed m-0">
                    {{ $ocSite['site_slogan'] }}
                </p>
                @if (! empty(trim((string) ($ocSite['footer_notice'] ?? ''))))
                    <div class="oc-site-footer__extra text-sm oc-site-footer__muted mt-4 [&_a]:oc-site-footer__link">{!! $ocSite['footer_notice'] !!}</div>
                @endif
            </div>
            <div>
                <h4 class="font-bold mb-3 oc-heading text-sm">快速链接</h4>
                <nav class="flex flex-wrap gap-x-4 gap-y-2 text-sm oc-site-footer__muted" aria-label="快速链接">
                    <a href="{{ route('home') }}" class="oc-site-footer__link">首页</a>
                    <a href="{{ route('articles.index') }}" class="oc-site-footer__link">文章</a>
                    <a href="{{ route('projects.index') }}" class="oc-site-footer__link">项目</a>
                    <a href="{{ route('vip') }}" class="oc-site-footer__link">VIP</a>
                </nav>
            </div>
            <div>
                <h4 class="font-bold mb-3 oc-heading text-sm">法律条款</h4>
                <nav class="flex flex-wrap gap-x-4 gap-y-2 text-sm oc-site-footer__muted" aria-label="法律条款">
                    <a href="{{ route('privacy') }}" class="oc-site-footer__link">隐私政策</a>
                    <a href="{{ route('terms') }}" class="oc-site-footer__link">服务条款</a>
                    <span class="oc-site-footer__muted">退款政策</span>
                </nav>
            </div>
            <div>
                <h4 class="font-bold mb-3 oc-heading text-sm">联系我们</h4>
                <ul class="space-y-2 text-sm oc-site-footer__muted list-none m-0 p-0">
                    @if (! empty(trim((string) ($ocSite['contact_email'] ?? ''))))
                        <li>📧 <a href="mailto:{{ $ocSite['contact_email'] }}" class="oc-site-footer__link">{{ $ocSite['contact_email'] }}</a></li>
                    @else
                        <li class="oc-site-footer__muted">📧 邮箱请见后台「系统与站点」配置</li>
                    @endif
                    @if (! empty(trim((string) ($ocSite['contact_wechat'] ?? ''))))
                        <li>💬 微信：{{ $ocSite['contact_wechat'] }}</li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="oc-site-footer__divider mt-10 pt-8 text-center text-sm oc-site-footer__muted">
            <p class="m-0">&copy; {{ date('Y') }} {{ $ocSite['site_name'] }}。All rights reserved.</p>
        </div>
    </div>
</footer>
