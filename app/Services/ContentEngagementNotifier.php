<?php

namespace App\Services;

use App\Models\Article;
use App\Models\InboxNotification;
use App\Models\SideHustleCase;
use App\Models\User;
use App\Models\UserPost;
use Illuminate\Database\Eloquent\Model;

/**
 * 点赞 / 收藏他人发布的内容时通知内容作者（不通知自己）。
 */
final class ContentEngagementNotifier
{
    public function notifyLiked(User $actor, Model $content): void
    {
        $this->notify($actor, $content, 'like_content', '点赞');
    }

    public function notifyFavorited(User $actor, Model $content): void
    {
        $this->notify($actor, $content, 'favorite_content', '收藏');
    }

    private function notify(User $actor, Model $content, string $type, string $verb): void
    {
        $ownerId = $this->ownerUserId($content);
        if ($ownerId <= 0 || $ownerId === (int) $actor->id) {
            return;
        }

        $actorName = $actor->name ?? '用户';
        $title = $this->titleOf($content);
        if ($title === '') {
            return;
        }

        $url = $this->urlOf($content);
        InboxNotification::query()->create([
            'user_id' => $ownerId,
            'type' => $type,
            'title' => '「'.$actorName.'」'.$verb.'了你的'.$this->kindLabel($content).'《'.$title.'》',
            'content' => '详情见标题；若有关联链接，可从通知跳转查看原文。',
            'action_url' => $url,
        ]);
    }

    private function ownerUserId(Model $content): int
    {
        return match (true) {
            $content instanceof Article => (int) ($content->author_id ?? 0),
            $content instanceof UserPost => (int) ($content->user_id ?? 0),
            $content instanceof SideHustleCase => (int) ($content->user_id ?? 0),
            default => 0,
        };
    }

    private function kindLabel(Model $content): string
    {
        return match (true) {
            $content instanceof Article => '文章',
            $content instanceof UserPost => '投稿',
            $content instanceof SideHustleCase => '案例',
            default => '内容',
        };
    }

    private function titleOf(Model $content): string
    {
        $t = match (true) {
            $content instanceof Article => (string) ($content->title ?? ''),
            $content instanceof UserPost => (string) ($content->title ?? ''),
            $content instanceof SideHustleCase => (string) ($content->title ?? ''),
            default => '',
        };

        return trim($t);
    }

    private function urlOf(Model $content): ?string
    {
        try {
            return match (true) {
                $content instanceof Article => route('articles.show', $content),
                $content instanceof UserPost => route('posts.show', $content),
                $content instanceof SideHustleCase => route('cases.show', $content),
                default => null,
            };
        } catch (\Throwable) {
            return null;
        }
    }
}
