<?php

namespace App\Filament\Resources;

use App\Models\User;
use App\Filament\Resources\UserResource\Pages;
use App\Support\FilamentUserSchema;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends BaseAdminResource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = '用户与会员';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = '前台用户';

    protected static ?string $pluralModelLabel = '前台用户';

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
        $core = [
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('email')->email()->required()->maxLength(255),
            Forms\Components\TextInput::make('password')->password()->revealable()
                ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                ->dehydrated(fn ($state) => filled($state))
                ->maxLength(255)
                ->helperText('留空表示不修改密码。'),
            Forms\Components\TextInput::make('avatar')->maxLength(2048)->url()->nullable(),
            Forms\Components\Textarea::make('bio')->columnSpanFull(),
            Forms\Components\TextInput::make('enterprise_wechat_id')->maxLength(255),
            Forms\Components\Toggle::make('privacy_mode'),
            Forms\Components\Select::make('role')
                ->options(['user' => '普通用户', 'vip' => 'VIP', 'svip' => 'SVIP', 'admin' => '管理员'])
                ->required(),
            Forms\Components\Toggle::make('is_banned'),
            Forms\Components\DateTimePicker::make('subscription_ends_at'),
            Forms\Components\TextInput::make('points_balance')->numeric(),
        ];

        return $form->schema(array_merge($core, FilamentUserSchema::autoFormComponents()));
    }

    public static function table(Table $table): Table
    {
        $columns = [
            Tables\Columns\TextColumn::make('id')->sortable(),
            Tables\Columns\TextColumn::make('name')->limit(40)->searchable()->toggleable(),
            Tables\Columns\TextColumn::make('email')->limit(40)->searchable()->toggleable(),
            Tables\Columns\ImageColumn::make('avatar')
                ->label('头像')
                ->circular()
                ->height(40)
                ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=U')
                ->toggleable(),
            Tables\Columns\TextColumn::make('role')
                ->label('角色')
                ->badge()
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    'user' => '普通用户',
                    'vip' => 'VIP',
                    'svip' => 'SVIP',
                    'admin' => '管理员',
                    default => (string) $state,
                })
                ->color(fn (?string $state): string => match ($state) {
                    'admin' => 'danger',
                    'svip' => 'warning',
                    'vip' => 'success',
                    default => 'gray',
                })
                ->toggleable(),
            Tables\Columns\IconColumn::make('is_banned')
                ->label('封禁')
                ->boolean()
                ->toggleable(),
            Tables\Columns\TextColumn::make('points_balance')
                ->label('积分')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('subscription_ends_at')
                ->label('订阅到期')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('last_login_at')
                ->label('上次登录')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('bio')->limit(30)->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('enterprise_wechat_id')
                ->limit(20)
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];

        return $table
            ->columns(static::searchableColumns(array_merge($columns, FilamentUserSchema::autoTableColumns())))
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
