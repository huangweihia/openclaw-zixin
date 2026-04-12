<?php

namespace App\Filament\Resources;

use App\Models\AdSlot;
use App\Filament\Resources\AdSlotResource\Pages;
use Filament\Notifications\Notification;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;

class AdSlotResource extends BaseAdminResource
{
    protected static ?string $model = AdSlot::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = '站点与外观';

    protected static ?int $navigationSort = 50;

    protected static ?string $modelLabel = '广告位';

    protected static ?string $pluralModelLabel = '广告位';

    /** @return array<string, string> */
    public static function positionOptions(): array
    {
        return [
            'top' => '顶部',
            'bottom' => '底部',
            'left' => '左侧',
            'right' => '右侧',
        ];
    }

    /** @return array<string, string> */
    public static function typeOptions(): array
    {
        return [
            'banner' => '横幅（图片）',
            'banner_video' => '横幅（视频）',
            'sidebar' => '侧栏',
            'inline' => '信息流',
            'popup' => '弹窗',
            'float' => '浮动角标',
        ];
    }

    /** @return array<string, string> */
    public static function audienceOptions(): array
    {
        return [
            'all' => '所有人',
            'guest' => '仅游客',
            'user' => '仅登录用户',
            'vip' => 'VIP（含管理员）',
            'svip' => 'SVIP（含管理员）',
            'admin' => '仅管理员',
            'member' => '会员（VIP/SVIP/管理员）',
            'non_member' => '非会员（游客/普通用户）',
        ];
    }

    public static function canCreate(): bool
    {
        return static::canViewAny() && true;
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny() && true;
    }

    public static function canDelete($record): bool
    {
        return static::canViewAny() && true;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->label('编码')
                    ->maxLength(128)
                    ->unique(ignoreRecord: true)
                    ->disabled()
                    ->dehydrated()
                    ->visibleOn('edit')
                    ->helperText('创建时根据「名称」自动生成，全站唯一，用于模板与接口引用。'),
                Forms\Components\Select::make('position')
                    ->options(static::positionOptions())
                    ->required()
                    ->native(false)
                    ->default('top'),
                Forms\Components\Select::make('type')
                    ->options(static::typeOptions())
                    ->required()
                    ->native(false)
                    ->default('banner'),
                Forms\Components\TextInput::make('width')
                    ->numeric()
                    ->nullable()
                    ->suffix('px'),
                Forms\Components\TextInput::make('height')
                    ->numeric()
                    ->nullable()
                    ->suffix('px'),
                Forms\Components\Toggle::make('is_active')
                    ->label('启用'),
                Forms\Components\TextInput::make('sort')
                    ->numeric()
                    ->default(0),
                Forms\Components\Select::make('audience')
                    ->options(static::audienceOptions())
                    ->required()
                    ->native(false)
                    ->default('all'),
            ]),
            Forms\Components\TextInput::make('default_title')
                ->maxLength(500)
                ->columnSpanFull(),
            Forms\Components\FileUpload::make('default_image_upload')
                ->label('兜底图片（上传）')
                ->image()
                ->disk('public')
                ->directory('ad-slots/fallback')
                ->visibility('public')
                ->maxSize(5120)
                ->nullable()
                ->helperText('上传后保存为站内可访问地址；与下方外链二选一即可。'),
            Forms\Components\TextInput::make('default_image_url_manual')
                ->label('兜底图片（外链）')
                ->url()
                ->maxLength(500)
                ->nullable()
                ->helperText('若图片托管在外部 CDN，可只填此处，无需上传。'),
            Forms\Components\TextInput::make('default_link_url')
                ->url()
                ->maxLength(500)
                ->nullable(),
            Forms\Components\Textarea::make('default_content')
                ->columnSpanFull()
                ->rows(6),
            Forms\Components\Toggle::make('show_default_when_empty')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('code')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('position')
                    ->formatStateUsing(fn (?string $state): string => static::positionOptions()[$state ?? ''] ?? ($state ?? '—'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('type')
                    ->formatStateUsing(fn (?string $state): string => static::typeOptions()[$state ?? ''] ?? ($state ?? '—'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('width')->toggleable(),
                Tables\Columns\TextColumn::make('height')->toggleable(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->toggleable(),
                Tables\Columns\ImageColumn::make('default_image_url')
                    ->label('兜底图')
                    ->square()
                    ->height(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('setActive')
                    ->label('设为启用')
                    ->icon('heroicon-o-play-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('启用当前广告位')
                    ->modalDescription('全站仅允许一个广告位处于启用状态；确认后其余广告位将自动关闭。')
                    ->visible(fn (AdSlot $record): bool => ! $record->is_active)
                    ->action(function (AdSlot $record): void {
                        $record->update(['is_active' => true]);
                        Notification::make()
                            ->success()
                            ->title('已设为当前启用的广告位')
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\AdSlotResource\Pages\ListAdSlots::route('/'),
            'create' => \App\Filament\Resources\AdSlotResource\Pages\CreateAdSlot::route('/create'),
            'edit' => \App\Filament\Resources\AdSlotResource\Pages\EditAdSlot::route('/{record}/edit'),
        ];
    }
}
