<?php

namespace App\Filament\Pages;

use App\Filament\Resources\CommentReportResource;
use App\Filament\Resources\CommentResource;
use App\Filament\Resources\PublishAuditResource;
use App\Filament\Resources\UserPostResource;
use App\Models\CommentReport;
use App\Models\PublishAudit;
use App\Models\UserPost;
use App\Models\User;
use App\Support\AdminNavRegistry;
use Filament\Pages\Page;

/**
 * 对齐旧版 Vue「投稿审核」工作台：聚合待办数量与常用入口。
 */
class ModerationHubPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string $view = 'filament.pages.moderation-hub';

    protected static ?string $slug = 'moderation-hub';

    protected static ?string $title = '审核工作台';

    public static function getNavigationLabel(): string
    {
        return AdminNavRegistry::navigationLabel('moderation-hub') ?? '审核工作台';
    }

    public static function getNavigationGroup(): ?string
    {
        return AdminNavRegistry::navigationGroupTitle('moderation-hub')
            ?? AdminNavRegistry::navigationGroupTitle('moderation')
            ?? '审核与社区';
    }

    public static function getNavigationSort(): ?int
    {
        return AdminNavRegistry::navigationSort('moderation-hub')
            ?? AdminNavRegistry::navigationSort('moderation')
            ?? 0;
    }

    public static function shouldRegisterNavigation(): bool
    {
        $u = auth()->user();

        return $u instanceof User
            && $u->role === 'admin'
            && ! $u->is_banned
            && ($u->allowsAdminMenuKey('moderation-hub') || $u->allowsAdminMenuKey('moderation'));
    }

    public function getPendingPostsCountProperty(): int
    {
        return UserPost::query()->where('status', 'pending')->count();
    }

    public function getPendingPublishAuditsCountProperty(): int
    {
        return PublishAudit::query()->where('status', 'pending')->count();
    }

    public function getPendingCommentReportsCountProperty(): int
    {
        return CommentReport::query()->where('status', 'pending')->count();
    }

    /**
     * @return array<int, array{label: string, description: string, url: string, badge: int|string|null}>
     */
    public function getQuickLinksProperty(): array
    {
        return [
            [
                'label' => '用户动态（投稿）',
                'description' => '审核、隐藏或调整用户发布内容。',
                'url' => UserPostResource::getUrl(),
                'badge' => $this->pendingPostsCount,
            ],
            [
                'label' => '发布审计',
                'description' => '发布流审核记录与状态处理。',
                'url' => PublishAuditResource::getUrl(),
                'badge' => $this->pendingPublishAuditsCount,
            ],
            [
                'label' => '评论管理',
                'description' => '查看与隐藏评论。',
                'url' => CommentResource::getUrl(),
                'badge' => null,
            ],
            [
                'label' => '评论举报',
                'description' => '处理用户举报的评论。',
                'url' => CommentReportResource::getUrl(),
                'badge' => $this->pendingCommentReportsCount,
            ],
        ];
    }
}
