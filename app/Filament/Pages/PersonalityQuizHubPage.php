<?php

namespace App\Filament\Pages;

use App\Filament\Resources\PersonalityDimensionResource;
use App\Filament\Resources\PersonalityQuestionOptionResource;
use App\Filament\Resources\PersonalityQuestionResource;
use App\Filament\Resources\PersonalityQuizPlayResource;
use App\Filament\Resources\PersonalityQuizSettingResource;
use App\Filament\Resources\PersonalityTypeResource;
use App\Models\User;
use App\Support\AdminNavRegistry;
use Filament\Pages\Page;

class PersonalityQuizHubPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static string $view = 'filament.pages.personality-quiz-hub';

    protected static ?string $navigationGroup = '人格测试';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'SBTI 测评管理';

    public static function getNavigationLabel(): string
    {
        return '测评管理';
    }

    public static function getNavigationGroup(): ?string
    {
        return AdminNavRegistry::navigationGroupTitle('personality-quiz') ?? parent::getNavigationGroup();
    }

    public static function getNavigationSort(): ?int
    {
        return AdminNavRegistry::navigationSort('personality-quiz') ?? parent::getNavigationSort();
    }

    public static function shouldRegisterNavigation(): bool
    {
        $u = auth()->user();

        return $u instanceof User
            && $u->role === 'admin'
            && ! $u->is_banned
            && $u->allowsAdminMenuKey('personality-quiz');
    }

    public function getManageUrlProperty(): string
    {
        $token = trim((string) config('services.personality_quiz.admin_token', ''));

        return $token !== ''
            ? url('/personality-quiz/manage?token='.urlencode($token))
            : url('/personality-quiz/manage');
    }

    /**
     * @return array<int, array{label: string, description: string, url: string}>
     */
    public function getFilamentLinksProperty(): array
    {
        return [
            [
                'label' => '人格维度',
                'description' => '维护维度编码、名称与排序。',
                'url' => PersonalityDimensionResource::getUrl(),
            ],
            [
                'label' => '测评题目',
                'description' => '按维度维护题干与启用状态。',
                'url' => PersonalityQuestionResource::getUrl(),
            ],
            [
                'label' => '题目选项',
                'description' => '维护选项文案、分值与排序。',
                'url' => PersonalityQuestionOptionResource::getUrl(),
            ],
            [
                'label' => '16 型结果',
                'description' => '维护类型代码、名称与配图等。',
                'url' => PersonalityTypeResource::getUrl(),
            ],
            [
                'label' => '测评开关与阈值',
                'description' => '启用状态、低匹配阈值等键值配置。',
                'url' => PersonalityQuizSettingResource::getUrl(),
            ],
            [
                'label' => '游玩记录',
                'description' => '查看用户/游客测评提交记录。',
                'url' => PersonalityQuizPlayResource::getUrl(),
            ],
        ];
    }
}
