<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishedUserPostTest extends TestCase
{
    use RefreshDatabase;

    private function makeApprovedPost(array $overrides = []): UserPost
    {
        $user = User::factory()->create();

        return UserPost::query()->create(array_merge([
            'user_id' => $user->id,
            'type' => 'experience',
            'title' => '测试投稿标题 '.uniqid(),
            'content' => str_repeat('这是用于列表与详情的正文，长度满足校验。', 4),
            'visibility' => 'public',
            'status' => 'approved',
            'audited_at' => now(),
        ], $overrides));
    }

    public function test_posts_index_lists_only_approved_public_feed(): void
    {
        $visible = $this->makeApprovedPost(['title' => '广场可见投稿']);
        $this->makeApprovedPost(['title' => '仅私有', 'visibility' => 'private']);

        UserPost::query()->create([
            'user_id' => $visible->user_id,
            'type' => 'question',
            'title' => '待审核不应出现',
            'content' => str_repeat('待审核正文。', 5),
            'visibility' => 'public',
            'status' => 'pending',
        ]);

        $response = $this->get(route('posts.index'));

        $response->assertOk();
        $response->assertSee('广场可见投稿', false);
        $response->assertDontSee('待审核不应出现', false);
        $response->assertDontSee('仅私有', false);
    }

    public function test_posts_show_returns_404_for_pending(): void
    {
        $user = User::factory()->create();
        $pending = UserPost::query()->create([
            'user_id' => $user->id,
            'type' => 'tool',
            'title' => '待审核',
            'content' => str_repeat('正文。', 5),
            'visibility' => 'public',
            'status' => 'pending',
        ]);

        $this->get(route('posts.show', $pending))->assertNotFound();
    }

    public function test_posts_show_displays_approved_public(): void
    {
        $post = $this->makeApprovedPost([
            'title' => '详情页标题',
            'content' => '# 标题'.PHP_EOL.PHP_EOL.'段落内容。',
        ]);

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('详情页标题', false);
    }

    public function test_posts_show_vip_teaser_for_guest(): void
    {
        $post = $this->makeApprovedPost([
            'visibility' => 'vip',
            'content' => str_repeat('VIP 专享正文内容。', 10),
        ]);

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('开通 VIP', false);
    }
}
