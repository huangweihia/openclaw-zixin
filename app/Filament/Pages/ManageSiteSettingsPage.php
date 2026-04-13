<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Models\User;
use App\Support\AdminNavRegistry;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ManageSiteSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.manage-site-settings';

    protected static ?string $navigationGroup = '站点与外观';

    protected static ?int $navigationSort = 8;

    protected static ?string $title = '站点与系统设置';

    /** @var array<int, string> */
    private const SETTING_KEYS = [
        'site_name',
        'site_slogan',
        'site_description',
        'site_logo_url',
        'contact_email',
        'contact_wechat',
        'footer_notice',
        'analytics_note',
        'register_gift_enabled',
        'register_gift_role',
        'register_gift_days',
        'register_points_bonus',
        'points_rule_login_daily',
        'points_rule_post_approved',
        'points_rule_post_liked_author',
        'points_rule_post_favorited_author',
        'points_rule_post_commented_author',
        'points_rule_boost_cost',
        'points_rule_boost_window_hours',
        'points_rule_boost_random_notify_users',
        'points_rule_boost_daily_cap_per_post',
        'pricing_vip_deadline',
        'pricing_svip_deadline',
        'pricing_vip_seats',
        'pricing_svip_seats',
        'pricing_vip_promo',
        'pricing_svip_promo',
        'mail_batch_enabled',
        'mail_batch_start_hour',
        'mail_batch_end_hour',
        'mail_sub_default_daily_time',
        'mail_sub_default_weekly_time',
        'mail_sub_weekly_send_weekday',
        'wechat_mini_subscribe_template_ids',
    ];

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return AdminNavRegistry::navigationLabel('settings') ?? '站点设置';
    }

    public static function getNavigationGroup(): ?string
    {
        return AdminNavRegistry::navigationGroupTitle('settings') ?? parent::getNavigationGroup();
    }

    public static function getNavigationSort(): ?int
    {
        return AdminNavRegistry::navigationSort('settings') ?? parent::getNavigationSort();
    }

    public static function shouldRegisterNavigation(): bool
    {
        $u = auth()->user();

        return $u instanceof User
            && $u->role === 'admin'
            && ! $u->is_banned
            && $u->allowsAdminMenuKey('settings');
    }

    public function mount(): void
    {
        $map = SiteSetting::allAsMap();
        $fill = [];
        foreach (self::SETTING_KEYS as $key) {
            $fill[$key] = $map[$key] ?? '';
        }
        $fill['site_logo_upload'] = null;
        $this->form->fill($fill);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('站点展示')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('site_name')
                            ->label('站点名称')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('site_slogan')
                            ->label('站点标语（首页 Hero 等）')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('site_description')
                            ->label('站点简介')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('site_logo_upload')
                            ->label('上传 Logo')
                            ->image()
                            ->disk('public')
                            ->directory('site')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->nullable()
                            ->helperText('上传后覆盖下方「Logo 地址」；也可只填地址不上传。'),
                        Forms\Components\TextInput::make('site_logo_url')
                            ->label('Logo 地址')
                            ->maxLength(500)
                            ->placeholder('完整 URL 或 /storage/... 相对路径')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('contact_email')
                            ->label('联系邮箱')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contact_wechat')
                            ->label('联系微信（页脚展示）')
                            ->maxLength(120),
                        Forms\Components\Textarea::make('footer_notice')
                            ->label('页脚提示 / HTML')
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('analytics_note')
                            ->label('统计 / 运营备注')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('注册与会员')
                    ->description('新用户邮箱注册或微信小程序首次注册成功后，若开启赠送：将角色设为 VIP/SVIP 并写入 subscription_ends_at；注册积分写入 points 流水。')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('register_gift_enabled')
                            ->label('开启注册赠送会员')
                            ->options(['1' => '开启', '0' => '关闭'])
                            ->native(false),
                        Forms\Components\Select::make('register_gift_role')
                            ->label('赠送角色')
                            ->options(['vip' => 'VIP', 'svip' => 'SVIP'])
                            ->native(false),
                        Forms\Components\TextInput::make('register_gift_days')
                            ->label('赠送天数（0 表示不赠送）')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(3650),
                        Forms\Components\TextInput::make('register_points_bonus')
                            ->label('注册赠送积分（0 关闭）')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(999999),
                    ]),
                Forms\Components\Section::make('价格营销（首页 / VIP 页）')
                    ->description('截止时间为未来时间点（建议 ISO8601）；名额为数字，用于「仅剩 N 席」文案。')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('pricing_vip_deadline')
                            ->label('VIP 优惠截止时间')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('pricing_svip_deadline')
                            ->label('SVIP 优惠截止时间')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('pricing_vip_seats')
                            ->label('VIP 剩余名额')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('pricing_svip_seats')
                            ->label('SVIP 剩余名额')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('pricing_vip_promo')
                            ->label('VIP 营销副标题')
                            ->maxLength(120)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('pricing_svip_promo')
                            ->label('SVIP 营销副标题')
                            ->maxLength(120)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('积分规则（节点固定，分值可调）')
                    ->description('仅调整分值，不改变触发节点。前台「积分与充值」页会同步展示此处配置。')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('points_rule_login_daily')
                            ->label('每日首次登录奖励积分')
                            ->numeric()
                            ->minValue(0)
                            ->default((string) config('points_rewards.login_daily', 5)),
                        Forms\Components\TextInput::make('points_rule_post_approved')
                            ->label('投稿审核通过奖励积分')
                            ->numeric()
                            ->minValue(0)
                            ->default((string) config('points_rewards.post_approved', 20)),
                        Forms\Components\TextInput::make('points_rule_post_liked_author')
                            ->label('投稿被点赞（作者）奖励')
                            ->numeric()
                            ->minValue(0)
                            ->default((string) config('points_rewards.post_liked_author', 2)),
                        Forms\Components\TextInput::make('points_rule_post_favorited_author')
                            ->label('投稿被收藏（作者）奖励')
                            ->numeric()
                            ->minValue(0)
                            ->default((string) config('points_rewards.post_favorited_author', 3)),
                        Forms\Components\TextInput::make('points_rule_post_commented_author')
                            ->label('投稿被评论（作者）奖励')
                            ->numeric()
                            ->minValue(0)
                            ->default((string) config('points_rewards.post_commented_author', 2)),
                        Forms\Components\TextInput::make('points_rule_boost_cost')
                            ->label('单次加热消耗积分')
                            ->numeric()
                            ->minValue(1)
                            ->default((string) config('boost.points_per_boost', 100)),
                        Forms\Components\TextInput::make('points_rule_boost_window_hours')
                            ->label('加热有效时长（小时）')
                            ->numeric()
                            ->minValue(1)
                            ->default((string) config('boost.window_hours', 72)),
                        Forms\Components\TextInput::make('points_rule_boost_random_notify_users')
                            ->label('加热随机触达用户数')
                            ->numeric()
                            ->minValue(0)
                            ->default((string) config('boost.random_notify_users', 15)),
                        Forms\Components\TextInput::make('points_rule_boost_daily_cap_per_post')
                            ->label('单用户单帖每日加热上限')
                            ->numeric()
                            ->minValue(1)
                            ->default((string) config('boost.max_boosts_per_actor_per_post_per_day', 3)),
                    ]),
                Forms\Components\Section::make('邮件批处理与订阅摘要')
                    ->description('摘要类邮件由 Laravel 调度每分钟触发一次命令，仅在「当前时刻 = 用户所选时间」时发送。用户未选时间则使用下方默认 HH:mm。周精选仅在下方「每周发送星期」当天、且到达用户所选时刻时发送。时间窗仍限制命令是否在允许小时内执行。')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('mail_batch_enabled')
                            ->label('启用邮件时间窗限制')
                            ->options(['1' => '开启', '0' => '关闭'])
                            ->native(false),
                        Forms\Components\TextInput::make('mail_batch_start_hour')
                            ->label('允许开始小时')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(23),
                        Forms\Components\TextInput::make('mail_batch_end_hour')
                            ->label('允许结束小时')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(23),
                        Forms\Components\TextInput::make('mail_sub_default_daily_time')
                            ->label('每日精选默认发送时刻')
                            ->placeholder('09:00')
                            ->helperText('HH:mm，用户未在个人中心填写每日时间时使用。')
                            ->maxLength(5),
                        Forms\Components\TextInput::make('mail_sub_default_weekly_time')
                            ->label('每周精选默认发送时刻')
                            ->placeholder('10:00')
                            ->helperText('HH:mm，用户未填写每周时间时使用。')
                            ->maxLength(5),
                        Forms\Components\Select::make('mail_sub_weekly_send_weekday')
                            ->label('每周精选发送星期（ISO）')
                            ->options([
                                '1' => '周一',
                                '2' => '周二',
                                '3' => '周三',
                                '4' => '周四',
                                '5' => '周五',
                                '6' => '周六',
                                '7' => '周日',
                            ])
                            ->native(false)
                            ->helperText('与 Carbon dayOfWeekIso 一致：1=周一，7=周日。'),
                    ]),
                Forms\Components\Section::make('微信小程序订阅消息')
                    ->description('当 .env 未配置 WECHAT_MINI_SUBSCRIBE_TEMPLATE_IDS 时，小程序「会员到期提醒」接口会读取此处。逗号分隔模板 ID，最多 3 个，须与公众平台「订阅消息 → 我的模板」一致。')
                    ->schema([
                        Forms\Components\TextInput::make('wechat_mini_subscribe_template_ids')
                            ->label('订阅消息模板 ID（逗号分隔）')
                            ->maxLength(500)
                            ->placeholder('例如：AbCdEf123...,XyZ789...')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $upload = $state['site_logo_upload'] ?? null;
        unset($state['site_logo_upload']);
        if (is_string($upload) && $upload !== '' && ! str_starts_with($upload, 'http://') && ! str_starts_with($upload, 'https://')) {
            $state['site_logo_url'] = Storage::disk('public')->url($upload);
        }
        foreach (self::SETTING_KEYS as $key) {
            $v = $state[$key] ?? null;
            SiteSetting::setValue($key, $v === null || $v === '' ? null : (string) $v);
        }
        $this->mount();
        Notification::make()
            ->success()
            ->title('已保存')
            ->body('系统与站点设置已写入 site_settings 表。')
            ->send();
    }
}
