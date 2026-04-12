<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Comment;
use App\Models\InboxNotification;
use App\Models\Project;
use App\Models\PrivateTrafficSop;
use App\Models\SideHustleCase;
use App\Models\UserPost;
use Illuminate\Support\Str;

/**
 * 评论回复后通知：被回复的评论作者 + 内容发帖人（去重、不通知自己）。
 */
final class CommentReplyNotifier
{
    public function notify(Comment $reply, Comment $repliedTo): void
    {
        $reply->loadMissing('user');
        $replier = $reply->user;
        $replierId = (int) $reply->user_id;
        $replierName = $replier?->name ?? '用户';

        $commentable = $reply->commentable;
        if ($commentable === null) {
            return;
        }

        $snippet = Str::limit(strip_tags((string) $reply->content), 160);
        $actionUrl = $this->contentUrl($commentable);
        $kind = $this->contentKind($commentable);

        $notified = [];

        $parentAuthorId = (int) $repliedTo->user_id;
        if ($parentAuthorId > 0 && $parentAuthorId !== $replierId) {
            InboxNotification::query()->create([
                'user_id' => $parentAuthorId,
                'type' => 'comment_reply',
                'title' => '「'.$replierName.'」回复了你的评论',
                'content' => $snippet,
                'action_url' => $actionUrl,
            ]);
            $notified[$parentAuthorId] = true;
        }

        $ownerId = $this->contentOwnerUserId($commentable);
        if ($ownerId > 0 && $ownerId !== $replierId && empty($notified[$ownerId])) {
            InboxNotification::query()->create([
                'user_id' => $ownerId,
                'type' => 'comment_reply',
                'title' => '「'.$replierName.'」在你的'.$kind.'下发表了回复',
                'content' => $snippet,
                'action_url' => $actionUrl,
            ]);
        }
    }

    /**
     * 顶层评论（非回复）通知内容作者。
     */
    public function notifyNewRootComment(Comment $comment): void
    {
        if ($comment->parent_id !== null) {
            return;
        }

        $comment->loadMissing('user');
        $commenterId = (int) $comment->user_id;
        $commenterName = $comment->user?->name ?? '用户';

        $commentable = $comment->commentable;
        if ($commentable === null) {
            return;
        }

        $ownerId = $this->contentOwnerUserId($commentable);
        if ($ownerId <= 0 || $ownerId === $commenterId) {
            return;
        }

        $snippet = Str::limit(strip_tags((string) $comment->content), 160);
        $actionUrl = $this->contentUrl($commentable);
        $kind = $this->contentKind($commentable);

        InboxNotification::query()->create([
            'user_id' => $ownerId,
            'type' => 'content_comment',
            'title' => '「'.$commenterName.'」评论了你的'.$kind,
            'content' => $snippet,
            'action_url' => $actionUrl,
        ]);
    }

    private function contentOwnerUserId(object $commentable): int
    {
        if ($commentable instanceof Article) {
            return (int) ($commentable->author_id ?? 0);
        }
        if ($commentable instanceof UserPost) {
            return (int) ($commentable->user_id ?? 0);
        }
        if ($commentable instanceof SideHustleCase) {
            return (int) ($commentable->user_id ?? 0);
        }

        return 0;
    }

    private function contentKind(object $commentable): string
    {
        return match (true) {
            $commentable instanceof Article => '文章',
            $commentable instanceof Project => '项目',
            $commentable instanceof UserPost => '投稿',
            $commentable instanceof SideHustleCase => '案例',
            $commentable instanceof PrivateTrafficSop => 'SOP',
            default => '内容',
        };
    }

    private function contentUrl(object $commentable): ?string
    {
        try {
            return match (true) {
                $commentable instanceof Article => route('articles.show', $commentable),
                $commentable instanceof Project => route('projects.show', $commentable),
                $commentable instanceof UserPost => route('posts.show', $commentable),
                $commentable instanceof SideHustleCase => route('cases.show', $commentable),
                $commentable instanceof PrivateTrafficSop => route('sops.show', $commentable),
                default => null,
            };
        } catch (\Throwable) {
            return null;
        }
    }
}
