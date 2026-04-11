<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * 在服务器执行：php artisan filament:generate-admin-resources
 * 根据下方清单生成 Filament v3 Resource + Pages（表单/表格由 fillable 自动推断，复杂字段可事后手改）。
 */
class GenerateFilamentAdminResources extends Command
{
    protected $signature = 'filament:generate-admin-resources {--force : 覆盖已存在的 Resource 文件}';

    protected $description = '批量生成后台 Filament Resource（与 admin_api / 方案文档对齐）';

    /** @var array<int, array<string, mixed>> */
    protected array $modules = [
        ['model' => \App\Models\Article::class, 'resource' => 'ArticleResource', 'list' => 'ListArticles', 'create' => 'CreateArticle', 'edit' => 'EditArticle', 'label' => '文章', 'group' => '内容与社区', 'icon' => 'heroicon-o-document-text', 'sort' => 10],
        ['model' => \App\Models\Category::class, 'resource' => 'CategoryResource', 'list' => 'ListCategories', 'create' => 'CreateCategory', 'edit' => 'EditCategory', 'label' => '分类', 'group' => '内容与社区', 'icon' => 'heroicon-o-folder', 'sort' => 20],
        ['model' => \App\Models\Project::class, 'resource' => 'ProjectResource', 'list' => 'ListProjects', 'create' => 'CreateProject', 'edit' => 'EditProject', 'label' => '项目', 'group' => '内容与社区', 'icon' => 'heroicon-o-cube', 'sort' => 30],
        ['model' => \App\Models\Comment::class, 'resource' => 'CommentResource', 'list' => 'ListComments', 'create' => null, 'edit' => 'EditComment', 'label' => '评论', 'group' => '内容与社区', 'icon' => 'heroicon-o-chat-bubble-left-right', 'sort' => 40, 'canCreate' => false],
        ['model' => \App\Models\CommentReport::class, 'resource' => 'CommentReportResource', 'list' => 'ListCommentReports', 'create' => null, 'edit' => 'EditCommentReport', 'label' => '评论举报', 'group' => '内容与社区', 'icon' => 'heroicon-o-flag', 'sort' => 50, 'canCreate' => false],
        ['model' => \App\Models\UserPost::class, 'resource' => 'UserPostResource', 'list' => 'ListUserPosts', 'create' => null, 'edit' => 'EditUserPost', 'label' => '用户动态', 'group' => '内容与社区', 'icon' => 'heroicon-o-newspaper', 'sort' => 60, 'canCreate' => false],
        ['model' => \App\Models\PublishAudit::class, 'resource' => 'PublishAuditResource', 'list' => 'ListPublishAudits', 'create' => null, 'edit' => 'EditPublishAudit', 'label' => '发布审计', 'group' => '内容与社区', 'icon' => 'heroicon-o-clipboard-document-check', 'sort' => 70, 'canCreate' => false],

        ['model' => \App\Models\User::class, 'resource' => 'UserResource', 'list' => 'ListUsers', 'create' => 'CreateUser', 'edit' => 'EditUser', 'label' => '前台用户', 'group' => '用户与会员', 'icon' => 'heroicon-o-users', 'sort' => 10, 'isUser' => true],
        ['model' => \App\Models\Point::class, 'resource' => 'PointResource', 'list' => 'ListPoints', 'create' => null, 'edit' => null, 'view' => 'ViewPoint', 'label' => '积分流水', 'group' => '用户与会员', 'icon' => 'heroicon-o-banknotes', 'sort' => 20, 'viewOnly' => true],
        ['model' => \App\Models\ViewHistory::class, 'resource' => 'ViewHistoryResource', 'list' => 'ListViewHistories', 'create' => null, 'edit' => null, 'view' => 'ViewViewHistory', 'label' => '浏览记录', 'group' => '用户与会员', 'icon' => 'heroicon-o-eye', 'sort' => 30, 'viewOnly' => true],

        ['model' => \App\Models\Order::class, 'resource' => 'OrderResource', 'list' => 'ListOrders', 'create' => null, 'edit' => 'EditOrder', 'label' => '订单', 'group' => '订单与财务', 'icon' => 'heroicon-o-shopping-cart', 'sort' => 10, 'canCreate' => false],
        ['model' => \App\Models\Subscription::class, 'resource' => 'SubscriptionResource', 'list' => 'ListSubscriptions', 'create' => null, 'edit' => 'EditSubscription', 'label' => '订阅', 'group' => '订单与财务', 'icon' => 'heroicon-o-credit-card', 'sort' => 20, 'canCreate' => false],
        ['model' => \App\Models\SvipSubscription::class, 'resource' => 'SvipSubscriptionResource', 'list' => 'ListSvipSubscriptions', 'create' => 'CreateSvipSubscription', 'edit' => 'EditSvipSubscription', 'label' => 'SVIP 订阅', 'group' => '订单与财务', 'icon' => 'heroicon-o-sparkles', 'sort' => 30],
        ['model' => \App\Models\SvipCustomSubscription::class, 'resource' => 'SvipCustomSubscriptionResource', 'list' => 'ListSvipCustomSubscriptions', 'create' => null, 'edit' => null, 'view' => 'ViewSvipCustomSubscription', 'label' => 'SVIP 定制', 'group' => '订单与财务', 'icon' => 'heroicon-o-adjustments-horizontal', 'sort' => 40, 'viewOnly' => true],
        ['model' => \App\Models\RefundRequest::class, 'resource' => 'RefundRequestResource', 'list' => 'ListRefundRequests', 'create' => null, 'edit' => 'EditRefundRequest', 'label' => '退款申请', 'group' => '订单与财务', 'icon' => 'heroicon-o-arrow-uturn-left', 'sort' => 50, 'canCreate' => false],
        ['model' => \App\Models\InvoiceRequest::class, 'resource' => 'InvoiceRequestResource', 'list' => 'ListInvoiceRequests', 'create' => null, 'edit' => 'EditInvoiceRequest', 'label' => '发票申请', 'group' => '订单与财务', 'icon' => 'heroicon-o-document-text', 'sort' => 60, 'canCreate' => false],

        ['model' => \App\Models\PremiumResource::class, 'resource' => 'MemberPremiumResource', 'list' => 'ListMemberPremiums', 'create' => 'CreateMemberPremium', 'edit' => 'EditMemberPremium', 'label' => '会员资源', 'group' => '资源与增长', 'icon' => 'heroicon-o-gift', 'sort' => 10],
        ['model' => \App\Models\SideHustleCase::class, 'resource' => 'SideHustleCaseResource', 'list' => 'ListSideHustleCases', 'create' => 'CreateSideHustleCase', 'edit' => 'EditSideHustleCase', 'label' => '副业案例', 'group' => '资源与增长', 'icon' => 'heroicon-o-briefcase', 'sort' => 20],
        ['model' => \App\Models\PrivateTrafficSop::class, 'resource' => 'PrivateTrafficSopResource', 'list' => 'ListPrivateTrafficSops', 'create' => 'CreatePrivateTrafficSop', 'edit' => 'EditPrivateTrafficSop', 'label' => '私域 SOP', 'group' => '资源与增长', 'icon' => 'heroicon-o-share', 'sort' => 30],
        ['model' => \App\Models\AiToolMonetization::class, 'resource' => 'AiToolMonetizationResource', 'list' => 'ListAiToolMonetizations', 'create' => 'CreateAiToolMonetization', 'edit' => 'EditAiToolMonetization', 'label' => 'AI 工具变现', 'group' => '资源与增长', 'icon' => 'heroicon-o-cpu-chip', 'sort' => 40],

        ['model' => \App\Models\Announcement::class, 'resource' => 'AnnouncementResource', 'list' => 'ListAnnouncements', 'create' => 'CreateAnnouncement', 'edit' => 'EditAnnouncement', 'label' => '公告', 'group' => '营销与触达', 'icon' => 'heroicon-o-megaphone', 'sort' => 10],
        ['model' => \App\Models\SystemNotification::class, 'resource' => 'SystemNotificationResource', 'list' => 'ListSystemNotifications', 'create' => 'CreateSystemNotification', 'edit' => 'EditSystemNotification', 'label' => '系统通知', 'group' => '营销与触达', 'icon' => 'heroicon-o-bell-alert', 'sort' => 20],
        ['model' => \App\Models\PushNotification::class, 'resource' => 'PushNotificationResource', 'list' => 'ListPushNotifications', 'create' => 'CreatePushNotification', 'edit' => 'EditPushNotification', 'label' => '推送记录', 'group' => '营销与触达', 'icon' => 'heroicon-o-paper-airplane', 'sort' => 30],
        ['model' => \App\Models\EmailTemplate::class, 'resource' => 'EmailTemplateResource', 'list' => 'ListEmailTemplates', 'create' => 'CreateEmailTemplate', 'edit' => 'EditEmailTemplate', 'label' => '邮件模板', 'group' => '营销与触达', 'icon' => 'heroicon-o-envelope', 'sort' => 40],
        ['model' => \App\Models\EmailLog::class, 'resource' => 'EmailLogResource', 'list' => 'ListEmailLogs', 'create' => null, 'edit' => null, 'view' => 'ViewEmailLog', 'label' => '邮件记录', 'group' => '营销与触达', 'icon' => 'heroicon-o-archive-box', 'sort' => 50, 'viewOnly' => true],
        ['model' => \App\Models\EmailSubscription::class, 'resource' => 'EmailSubscriptionResource', 'list' => 'ListEmailSubscriptions', 'create' => 'CreateEmailSubscription', 'edit' => 'EditEmailSubscription', 'label' => '邮件订阅', 'group' => '营销与触达', 'icon' => 'heroicon-o-user-plus', 'sort' => 60],
        ['model' => \App\Models\EmailSetting::class, 'resource' => 'EmailSettingResource', 'list' => 'ListEmailSettings', 'create' => 'CreateEmailSetting', 'edit' => 'EditEmailSetting', 'label' => '邮件配置', 'group' => '营销与触达', 'icon' => 'heroicon-o-cog-6-tooth', 'sort' => 70],

        ['model' => \App\Models\SiteSetting::class, 'resource' => 'SiteSettingResource', 'list' => 'ListSiteSettings', 'create' => 'CreateSiteSetting', 'edit' => 'EditSiteSetting', 'label' => '站点设置', 'group' => '站点与外观', 'icon' => 'heroicon-o-globe-alt', 'sort' => 10],
        ['model' => \App\Models\SiteTestimonial::class, 'resource' => 'SiteTestimonialResource', 'list' => 'ListSiteTestimonials', 'create' => 'CreateSiteTestimonial', 'edit' => 'EditSiteTestimonial', 'label' => '首页评价', 'group' => '站点与外观', 'icon' => 'heroicon-o-star', 'sort' => 20],
        ['model' => \App\Models\SkinConfig::class, 'resource' => 'SkinConfigResource', 'list' => 'ListSkinConfigs', 'create' => 'CreateSkinConfig', 'edit' => 'EditSkinConfig', 'label' => '皮肤配置', 'group' => '站点与外观', 'icon' => 'heroicon-o-swatch', 'sort' => 30],
        ['model' => \App\Models\UserSkin::class, 'resource' => 'UserSkinResource', 'list' => 'ListUserSkins', 'create' => null, 'edit' => 'EditUserSkin', 'label' => '用户皮肤', 'group' => '站点与外观', 'icon' => 'heroicon-o-paint-brush', 'sort' => 40, 'canCreate' => false],
        ['model' => \App\Models\AdSlot::class, 'resource' => 'AdSlotResource', 'list' => 'ListAdSlots', 'create' => 'CreateAdSlot', 'edit' => 'EditAdSlot', 'label' => '广告位', 'group' => '站点与外观', 'icon' => 'heroicon-o-photo', 'sort' => 50],

        ['model' => \App\Models\OpenclawTaskLog::class, 'resource' => 'OpenclawTaskLogResource', 'list' => 'ListOpenclawTaskLogs', 'create' => null, 'edit' => null, 'view' => 'ViewOpenclawTaskLog', 'label' => 'OpenClaw 日志', 'group' => '运营与自动化', 'icon' => 'heroicon-o-command-line', 'sort' => 10, 'viewOnly' => true, 'canDelete' => true],
        ['model' => \App\Models\AuditLog::class, 'resource' => 'AuditLogResource', 'list' => 'ListAuditLogs', 'create' => null, 'edit' => null, 'view' => 'ViewAuditLog', 'label' => '审计日志', 'group' => '运营与自动化', 'icon' => 'heroicon-o-shield-check', 'sort' => 20, 'viewOnly' => true],

        ['model' => \App\Models\PersonalityDimension::class, 'resource' => 'PersonalityDimensionResource', 'list' => 'ListPersonalityDimensions', 'create' => 'CreatePersonalityDimension', 'edit' => 'EditPersonalityDimension', 'label' => '人格维度', 'group' => '人格测试', 'icon' => 'heroicon-o-squares-2x2', 'sort' => 10],
        ['model' => \App\Models\PersonalityQuestion::class, 'resource' => 'PersonalityQuestionResource', 'list' => 'ListPersonalityQuestions', 'create' => 'CreatePersonalityQuestion', 'edit' => 'EditPersonalityQuestion', 'label' => '测评题目', 'group' => '人格测试', 'icon' => 'heroicon-o-question-mark-circle', 'sort' => 20],
        ['model' => \App\Models\PersonalityQuestionOption::class, 'resource' => 'PersonalityQuestionOptionResource', 'list' => 'ListPersonalityQuestionOptions', 'create' => 'CreatePersonalityQuestionOption', 'edit' => 'EditPersonalityQuestionOption', 'label' => '题目选项', 'group' => '人格测试', 'icon' => 'heroicon-o-list-bullet', 'sort' => 30],
        ['model' => \App\Models\PersonalityType::class, 'resource' => 'PersonalityTypeResource', 'list' => 'ListPersonalityTypes', 'create' => 'CreatePersonalityType', 'edit' => 'EditPersonalityType', 'label' => '人格类型', 'group' => '人格测试', 'icon' => 'heroicon-o-user-circle', 'sort' => 40],
        ['model' => \App\Models\PersonalityQuizSetting::class, 'resource' => 'PersonalityQuizSettingResource', 'list' => 'ListPersonalityQuizSettings', 'create' => 'CreatePersonalityQuizSetting', 'edit' => 'EditPersonalityQuizSetting', 'label' => '测评设置', 'group' => '人格测试', 'icon' => 'heroicon-o-cog', 'sort' => 50],
        ['model' => \App\Models\PersonalityQuizPlay::class, 'resource' => 'PersonalityQuizPlayResource', 'list' => 'ListPersonalityQuizPlays', 'create' => null, 'edit' => null, 'view' => 'ViewPersonalityQuizPlay', 'label' => '测评记录', 'group' => '人格测试', 'icon' => 'heroicon-o-clipboard-document-list', 'sort' => 60, 'viewOnly' => true],

        ['model' => \App\Models\AdminUser::class, 'resource' => 'AdminUserResource', 'list' => 'ListAdminUsers', 'create' => 'CreateAdminUser', 'edit' => 'EditAdminUser', 'label' => '后台用户档案', 'group' => '系统', 'icon' => 'heroicon-o-identification', 'sort' => 10],
        ['model' => \App\Models\AdminRole::class, 'resource' => 'AdminRoleResource', 'list' => 'ListAdminRoles', 'create' => 'CreateAdminRole', 'edit' => 'EditAdminRole', 'label' => '后台角色', 'group' => '系统', 'icon' => 'heroicon-o-key', 'sort' => 20],
        ['model' => \App\Models\AdminPermission::class, 'resource' => 'AdminPermissionResource', 'list' => 'ListAdminPermissions', 'create' => null, 'edit' => null, 'view' => 'ViewAdminPermission', 'label' => '权限字典', 'group' => '系统', 'icon' => 'heroicon-o-lock-closed', 'sort' => 30, 'viewOnly' => true],
        ['model' => \App\Models\AdminNavSection::class, 'resource' => 'AdminNavSectionResource', 'list' => 'ListAdminNavSections', 'create' => 'CreateAdminNavSection', 'edit' => 'EditAdminNavSection', 'label' => '导航分区', 'group' => '系统', 'icon' => 'heroicon-o-bars-3-bottom-left', 'sort' => 40],
        ['model' => \App\Models\AdminNavItem::class, 'resource' => 'AdminNavItemResource', 'list' => 'ListAdminNavItems', 'create' => 'CreateAdminNavItem', 'edit' => 'EditAdminNavItem', 'label' => '导航项', 'group' => '系统', 'icon' => 'heroicon-o-link', 'sort' => 50],
    ];

    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $base = app_path('Filament/Resources');
        $count = 0;

        foreach ($this->modules as $mod) {
            $modelClass = $mod['model'];
            $resourceClass = $mod['resource'];
            $path = $base.'/'.$resourceClass.'.php';
            if (! $force && File::exists($path)) {
                $this->line("跳过已存在: {$resourceClass}");

                continue;
            }

            $modelBasename = class_basename($modelClass);
            $ns = 'App\\Filament\\Resources\\'.$resourceClass.'\\Pages';
            $pagesDir = $base.'/'.$resourceClass.'/Pages';
            File::ensureDirectoryExists($pagesDir);

            $formSchema = $this->buildFormSchema($modelClass, (bool) ($mod['isUser'] ?? false));
            $tableCols = $this->buildTableColumns($modelClass);
            $viewOnly = (bool) ($mod['viewOnly'] ?? false);
            $canCreate = $viewOnly ? false : ($mod['canCreate'] ?? true);
            $canEdit = $viewOnly ? false : ($mod['edit'] !== null);
            $canDelete = array_key_exists('canDelete', $mod)
                ? (bool) $mod['canDelete']
                : (! $viewOnly);

            $pagesPhp = $this->buildGetPagesArray($mod, $resourceClass, $viewOnly);
            $infolistSchema = $viewOnly ? $this->buildInfolistSchema($modelClass) : '';

            $resourceBody = $this->renderResourceClass(
                $resourceClass,
                $modelClass,
                $mod['label'],
                $mod['group'],
                $mod['icon'],
                (int) $mod['sort'],
                $formSchema,
                $tableCols,
                $pagesPhp,
                $infolistSchema,
                $viewOnly,
                $canCreate,
                $canEdit,
                $canDelete,
                $modelBasename === 'Article' ? 'id' : null
            );

            File::put($path, $resourceBody);
            $count++;

            $this->writeListPage($pagesDir, $mod['list'], $resourceClass, $ns, $canCreate);
            if ($viewOnly) {
                $this->writeViewPage($pagesDir, $mod['view'], $resourceClass, $ns);
            } else {
                if ($mod['create']) {
                    $this->writeCreatePage($pagesDir, $mod['create'], $resourceClass, $ns);
                }
                if ($mod['edit']) {
                    $this->writeEditPage($pagesDir, $mod['edit'], $resourceClass, $ns);
                }
            }
        }

        $this->info("已写入/更新 {$count} 个 Resource 主文件（含 Pages）。可执行 php artisan optimize:clear");

        return self::SUCCESS;
    }

    protected function buildGetPagesArray(array $mod, string $resourceClass, bool $viewOnly): string
    {
        $pagesBase = '\\App\\Filament\\Resources\\'.$resourceClass.'\\Pages\\';
        $listFqn = $pagesBase.$mod['list'];
        $lines = ["'index' => {$listFqn}::route('/'),"];
        if ($viewOnly) {
            $viewFqn = $pagesBase.$mod['view'];
            $lines[] = "'view' => {$viewFqn}::route('/{record}'),";

            return implode("\n            ", $lines);
        }
        if ($mod['create']) {
            $createFqn = $pagesBase.$mod['create'];
            $lines[] = "'create' => {$createFqn}::route('/create'),";
        }
        if ($mod['edit']) {
            $editFqn = $pagesBase.$mod['edit'];
            $lines[] = "'edit' => {$editFqn}::route('/{record}/edit'),";
        }

        return implode("\n            ", $lines);
    }

    /** @param class-string $modelClass */
    protected function buildInfolistSchema(string $modelClass): string
    {
        $m = new $modelClass;
        $fillable = $m->getFillable();
        $fields = array_values(array_unique(array_merge(['id'], $fillable, ['created_at', 'updated_at'])));
        $lines = [];
        foreach (array_slice($fields, 0, 24) as $field) {
            $lines[] = "Infolists\\Components\\TextEntry::make('{$field}')->columnSpanFull()";
        }

        return implode(",\n                ", $lines);
    }

    protected function renderResourceClass(
        string $resourceClass,
        string $modelClass,
        string $label,
        string $group,
        string $icon,
        int $sort,
        string $formSchema,
        string $tableCols,
        string $pagesPhp,
        string $infolistSchema,
        bool $viewOnly,
        bool $canCreate,
        bool $canEdit,
        bool $canDelete,
        ?string $recordKey
    ): string {
        $modelImport = $modelClass;
        $modelShort = class_basename($modelClass);
        $recordKeyMethod = $recordKey ? <<<PHP

    public static function getRecordRouteKeyName(): ?string
    {
        return '{$recordKey}';
    }
PHP
            : '';

        $canCreateStr = $canCreate ? 'true' : 'false';
        $canEditStr = $canEdit ? 'true' : 'false';
        $canDeleteStr = $canDelete ? 'true' : 'false';

        $tableActions = $this->buildTableActionsPhp($canEdit, $canDelete);
        $bulkActionsPhp = $canDelete
            ? '->bulkActions([
                Tables\\Actions\\DeleteBulkAction::make(),
            ])'
            : '->bulkActions([])';

        $infolistUse = $viewOnly ? <<<'PHP'
use Filament\Infolists;
use Filament\Infolists\Infolist;

PHP
            : '';

        $infolistMethod = $viewOnly ? <<<PHP

    public static function infolist(Infolist \$infolist): Infolist
    {
        return \$infolist->schema([
            {$infolistSchema}
        ]);
    }
PHP
            : '';

        return <<<PHP
<?php

namespace App\\Filament\\Resources;

use {$modelImport};
use App\\Filament\\Resources\\{$resourceClass}\\Pages;
use Filament\\Forms;
use Filament\\Forms\\Form;
use Filament\\Resources\\Resource;
use Filament\\Tables;
use Filament\\Tables\\Table;
{$infolistUse}class {$resourceClass} extends Resource
{
    protected static ?string \$model = {$modelShort}::class;

    protected static ?string \$navigationIcon = '{$icon}';

    protected static ?string \$navigationGroup = '{$group}';

    protected static ?int \$navigationSort = {$sort};

    protected static ?string \$modelLabel = '{$label}';

    protected static ?string \$pluralModelLabel = '{$label}';

    public static function canViewAny(): bool
    {
        \$u = auth()->user();
        return \$u && \$u->role === 'admin' && ! \$u->is_banned;
    }

    public static function canCreate(): bool
    {
        return static::canViewAny() && {$canCreateStr};
    }

    public static function canEdit(\$record): bool
    {
        return static::canViewAny() && {$canEditStr};
    }

    public static function canDelete(\$record): bool
    {
        return static::canViewAny() && {$canDeleteStr};
    }

    public static function form(Form \$form): Form
    {
        return \$form->schema([
            {$formSchema}
        ]);
    }

    public static function table(Table \$table): Table
    {
        return \$table
            ->columns([
                {$tableCols}
            ])
            ->actions([
                {$tableActions}
            ])
            {$bulkActionsPhp};
    }
{$infolistMethod}
    public static function getPages(): array
    {
        return [
            {$pagesPhp}
        ];
    }
{$recordKeyMethod}
}

PHP;
    }

    protected function buildTableActionsPhp(bool $canEdit, bool $canDelete): string
    {
        $parts = [];
        if (! $canEdit && ! $canDelete) {
            $parts[] = 'Tables\\Actions\\ViewAction::make()';
        } else {
            if (! $canEdit) {
                $parts[] = 'Tables\\Actions\\ViewAction::make()';
            }
            if ($canEdit) {
                $parts[] = 'Tables\\Actions\\EditAction::make()';
            }
            if ($canDelete) {
                $parts[] = 'Tables\\Actions\\DeleteAction::make()';
            }
        }

        return implode(",\n                ", $parts);
    }

    /** @param class-string $modelClass */
    protected function buildFormSchema(string $modelClass, bool $isUser): string
    {
        /** @var \Illuminate\Database\Eloquent\Model $m */
        $m = new $modelClass;
        $fillable = $m->getFillable();
        $casts = $m->getCasts();
        $lines = [];

        if ($isUser) {
            return <<<'PHP'
Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('email')->email()->required()->maxLength(255),
                Forms\Components\TextInput::make('password')->password()->revealable()
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255),
                Forms\Components\TextInput::make('avatar')->maxLength(2048),
                Forms\Components\Textarea::make('bio')->columnSpanFull(),
                Forms\Components\TextInput::make('enterprise_wechat_id')->maxLength(255),
                Forms\Components\Toggle::make('privacy_mode'),
                Forms\Components\Select::make('role')->options(['user' => 'user', 'vip' => 'vip', 'svip' => 'svip', 'admin' => 'admin'])->required(),
                Forms\Components\Toggle::make('is_banned'),
                Forms\Components\DateTimePicker::make('subscription_ends_at'),
                Forms\Components\TextInput::make('points_balance')->numeric(),
PHP;
        }

        foreach ($fillable as $field) {
            if ($field === 'password' && $modelClass === \App\Models\User::class) {
                continue;
            }
            $lines[] = $this->inferFieldComponent($field, $casts[$field] ?? null);
        }

        return implode(",\n                ", $lines);
    }

    protected function inferFieldComponent(string $field, mixed $cast): string
    {
        if (in_array($field, ['content', 'description', 'summary', 'bio', 'reason', 'admin_note', 'audit_note', 'keywords', 'exclude_keywords', 'sources', 'push_methods'], true)
            || str_contains($field, 'content')) {
            return "Forms\\Components\\Textarea::make('{$field}')->columnSpanFull()->rows(6)";
        }
        if (str_ends_with($field, '_at') || $field === 'published_at' || $field === 'processed_at' || $field === 'paid_at' || $field === 'expires_at' || $field === 'started_at' || $field === 'last_fetch_at' || $field === 'audited_at') {
            return "Forms\\Components\\DateTimePicker::make('{$field}')";
        }
        if ($cast === 'boolean' || str_starts_with($field, 'is_')) {
            return "Forms\\Components\\Toggle::make('{$field}')";
        }
        if ($cast === 'integer' || str_ends_with($field, '_count') || $field === 'sort' || $field === 'priority') {
            return "Forms\\Components\\TextInput::make('{$field}')->numeric()";
        }
        if ($cast === 'decimal:2' || $field === 'amount' || str_contains($field, 'amount') || $field === 'refund_amount') {
            return "Forms\\Components\\TextInput::make('{$field}')->numeric()->step(0.01)";
        }
        if ($cast === 'array' || $cast === 'json') {
            return "Forms\\Components\\Textarea::make('{$field}')->columnSpanFull()->helperText('JSON 数组，可手填')";
        }
        if (str_ends_with($field, '_id')) {
            return "Forms\\Components\\TextInput::make('{$field}')->numeric()->label('{$field}')";
        }

        return "Forms\\Components\\TextInput::make('{$field}')->maxLength(65535)";
    }

    /** @param class-string $modelClass */
    protected function buildTableColumns(string $modelClass): string
    {
        $m = new $modelClass;
        $fillable = $m->getFillable();
        $take = array_slice($fillable, 0, 6);
        $cols = [];
        $cols[] = "Tables\\Columns\\TextColumn::make('id')->sortable()";
        foreach ($take as $f) {
            $cols[] = "Tables\\Columns\\TextColumn::make('{$f}')->limit(40)->toggleable()";
        }
        $cols[] = "Tables\\Columns\\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)";

        return implode(",\n                ", $cols);
    }

    protected function writeListPage(string $dir, string $class, string $resourceClass, string $ns, bool $canCreate): void
    {
        $headerBlock = $canCreate
            ? <<<'PHP'
    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
PHP
            : <<<'PHP'
    protected function getHeaderActions(): array
    {
        return [];
    }
PHP;

        $php = <<<PHP
<?php

namespace {$ns};

use App\\Filament\\Resources\\{$resourceClass};
use Filament\\Resources\\Pages\\ListRecords;

class {$class} extends ListRecords
{
    protected static string \$resource = {$resourceClass}::class;

{$headerBlock}
}

PHP;
        File::put($dir.'/'.$class.'.php', $php);
    }

    protected function writeCreatePage(string $dir, string $class, string $resourceClass, string $ns): void
    {
        $php = <<<PHP
<?php

namespace {$ns};

use App\\Filament\\Resources\\{$resourceClass};
use Filament\\Resources\\Pages\\CreateRecord;

class {$class} extends CreateRecord
{
    protected static string \$resource = {$resourceClass}::class;
}

PHP;
        File::put($dir.'/'.$class.'.php', $php);
    }

    protected function writeEditPage(string $dir, string $class, string $resourceClass, string $ns): void
    {
        $php = <<<PHP
<?php

namespace {$ns};

use App\\Filament\\Resources\\{$resourceClass};
use Filament\\Resources\\Pages\\EditRecord;

class {$class} extends EditRecord
{
    protected static string \$resource = {$resourceClass}::class;
}

PHP;
        File::put($dir.'/'.$class.'.php', $php);
    }

    protected function writeViewPage(string $dir, string $class, string $resourceClass, string $ns): void
    {
        $php = <<<PHP
<?php

namespace {$ns};

use App\\Filament\\Resources\\{$resourceClass};
use Filament\\Resources\\Pages\\ViewRecord;

class {$class} extends ViewRecord
{
    protected static string \$resource = {$resourceClass}::class;
}

PHP;
        File::put($dir.'/'.$class.'.php', $php);
    }
}
