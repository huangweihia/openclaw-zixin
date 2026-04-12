<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SystemNotificationResource\Pages;
use App\Filament\Resources\BaseAdminResource;
use App\Models\SystemNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SystemNotificationResource extends BaseAdminResource
{
    protected static ?string $model = SystemNotification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationGroup = '营销与触达';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = '系统通知';

    protected static ?string $pluralModelLabel = '系统通知';

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

    /** @return array<string, string> */
    public static function priorityOptions(): array
    {
        return [
            'low' => '低',
            'medium' => '中',
            'high' => '高',
        ];
    }

    /** @return array<string, string> */
    public static function typeOptions(): array
    {
        return [
            'system' => '系统',
            'announcement' => '公告',
            'maintenance' => '维护',
        ];
    }

    /** @return array<string, string> */
    public static function audienceOptions(): array
    {
        return [
            'all' => '全部用户',
            'user' => '登录用户',
            'member' => '会员（VIP/SVIP）',
            'non_member' => '非会员',
            'vip' => 'VIP',
            'svip' => 'SVIP',
            'admin' => '管理员',
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('creator');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->maxLength(255)
                ->required()
                ->columnSpanFull(),
            Forms\Components\Textarea::make('content')
                ->rows(6)
                ->required()
                ->columnSpanFull(),
            Forms\Components\Select::make('priority')
                ->options(static::priorityOptions())
                ->required()
                ->native(false),
            Forms\Components\Select::make('type')
                ->options(static::typeOptions())
                ->required()
                ->native(false),
            Forms\Components\Select::make('audience')
                ->options(static::audienceOptions())
                ->required()
                ->native(false),
            Forms\Components\TextInput::make('action_url')
                ->maxLength(255)
                ->url()
                ->nullable(),
            Forms\Components\Toggle::make('is_published')
                ->inline(false),
            Forms\Components\DateTimePicker::make('expires_at')
                ->native(false),
            Forms\Components\Select::make('created_by')
                ->relationship('creator', 'name')
                ->searchable()
                ->preload()
                ->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->limit(36)
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => static::priorityOptions()[$state] ?? (string) $state),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => static::typeOptions()[$state] ?? (string) $state),
                Tables\Columns\TextColumn::make('audience')
                    ->badge()
                    ->toggleable()
                    ->formatStateUsing(fn (?string $state): string => static::audienceOptions()[$state] ?? (string) $state),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('创建人')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->label('已发布'),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
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
            'index' => Pages\ListSystemNotifications::route('/'),
            'create' => Pages\CreateSystemNotification::route('/create'),
            'edit' => Pages\EditSystemNotification::route('/{record}/edit'),
        ];
    }
}
