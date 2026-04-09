<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_spa_shell_ok_for_guest(): void
    {
        $this->get('/admin')->assertOk()->assertSee('admin-app', false);
    }

    public function test_admin_api_login_success_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->postJson('/api/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('user.email', $admin->email)
            ->assertJsonPath('user.role', 'admin');
    }

    public function test_admin_api_login_rejects_non_admin(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->postJson('/api/admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertStatus(422);
    }

    public function test_admin_dashboard_stats_requires_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $this->getJson('/api/admin/dashboard/stats')
            ->assertOk()
            ->assertJsonStructure([
                'summary',
                'todo',
                'recent_pending_posts',
                'recent_pending_orders',
                'runtime' => ['app_env', 'docker_compose_hint'],
            ]);
    }

    public function test_admin_users_index_ok_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->count(2)->create();

        $this->actingAs($admin);
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $this->getJson('/api/admin/users')->assertOk()->assertJsonStructure(['data', 'current_page']);
    }

    public function test_admin_upload_image_returns_public_url(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $file = UploadedFile::fake()->image('slot.jpg', 400, 100);

        $this->post('/api/admin/uploads/image', [
            'image' => $file,
        ])
            ->assertOk()
            ->assertJsonStructure(['url']);

        $this->assertCount(1, Storage::disk('public')->files('uploads/admin'));
    }

    public function test_admin_announcements_index_and_store(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $this->getJson('/api/admin/announcements')
            ->assertOk()
            ->assertJsonStructure(['announcements']);

        $this->postJson('/api/admin/announcements', [
            'title' => '集成测试公告标题',
            'content' => '<p>内容</p>',
            'priority' => 'high',
            'is_published' => true,
        ])
            ->assertCreated()
            ->assertJsonPath('announcement.title', '集成测试公告标题')
            ->assertJsonPath('announcement.is_published', true)
            ->assertJsonPath('announcement.created_by', $admin->id);
    }
}
