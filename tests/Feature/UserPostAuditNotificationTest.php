<?php

namespace Tests\Feature;

use App\Models\InboxNotification;
use App\Models\User;
use App\Models\UserPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPostAuditNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_approve_creates_inbox_notification_for_author(): void
    {
        $author = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $post = UserPost::query()->create([
            'user_id' => $author->id,
            'type' => 'experience',
            'title' => '审核通知测试稿',
            'content' => str_repeat('正文。', 8),
            'visibility' => 'public',
            'status' => 'pending',
        ]);

        $this->actingAs($admin);
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $this->postJson('/api/admin/user-posts/'.$post->id.'/approve')->assertOk();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $author->id,
            'type' => 'user_post_approved',
            'is_read' => false,
        ]);

        $n = InboxNotification::query()->where('user_id', $author->id)->first();
        $this->assertStringContainsString('已通过审核', $n->title);
        $this->assertNotEmpty($n->action_url);
    }

    public function test_reject_creates_inbox_notification_with_reason(): void
    {
        $author = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $post = UserPost::query()->create([
            'user_id' => $author->id,
            'type' => 'question',
            'title' => '将被拒绝的稿',
            'content' => str_repeat('正文。', 8),
            'visibility' => 'public',
            'status' => 'pending',
        ]);

        $this->actingAs($admin);
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $this->postJson('/api/admin/user-posts/'.$post->id.'/reject', [
            'audit_note' => '内容与规范不符，请修改后重提。',
        ])->assertOk();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $author->id,
            'type' => 'user_post_rejected',
        ]);

        $n = InboxNotification::query()->where('user_id', $author->id)->first();
        $this->assertStringContainsString('内容与规范不符', $n->content);
    }

    public function test_author_can_open_notification_and_mark_read(): void
    {
        $author = User::factory()->create();
        $post = UserPost::query()->create([
            'user_id' => $author->id,
            'type' => 'tool',
            'title' => '已发布',
            'content' => str_repeat('正文。', 8),
            'visibility' => 'public',
            'status' => 'approved',
            'audited_at' => now(),
        ]);

        $n = InboxNotification::query()->create([
            'user_id' => $author->id,
            'type' => 'user_post_approved',
            'title' => '投稿已通过审核',
            'content' => '测试',
            'action_url' => route('posts.show', $post),
        ]);

        $this->actingAs($author)->get(route('notifications.open', $n))
            ->assertRedirect(route('posts.show', $post));

        $this->assertTrue($n->fresh()->is_read);
    }
}
