<?php

namespace Tests\Feature;

use Tests\TestCase;

class SkinSwitcherTest extends TestCase
{
    public function test_skin_switcher_panel_is_rendered(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('blade-skin-switcher');
        $response->assertSee('skins.css');
        $response->assertSee('blade-skin-mount');
    }
}

