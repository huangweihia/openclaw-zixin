<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomeAndAuthPagesTest extends TestCase
{
    public function test_home_page_renders_prototype_content_not_laravel_boilerplate(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('🦀', false);
        $response->assertSee('OpenClaw 智信', false);
        $response->assertSee('免费试用 7 天', false);
        $response->assertSee('VIP 内容预览', false);
        $response->assertSee('©', false);
        $response->assertDontSee('Laravel News');
        $response->assertDontSee('Documentation');
        $response->assertDontSee('Vibrant Ecosystem');
    }

    public function test_register_page_follows_prototype_structure(): void
    {
        $response = $this->get('/register');

        $response->assertOk();
        $response->assertSee('免费注册 OpenClaw 智信', false);
        $response->assertSee('邮箱地址', false);
        $response->assertSee('获取验证码', false);
        $response->assertSee('同意协议并注册', false);
        $response->assertSee('class="site-footer"', false);
    }

    public function test_login_page_uses_layout_and_footer(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('欢迎回来', false);
        $response->assertSee('OpenClaw 智信', false);
    }
}
