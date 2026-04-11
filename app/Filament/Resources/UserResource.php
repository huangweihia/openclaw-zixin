<?php

namespace App\Filament\Resources;

use App\Models\User;
use App\Filament\Resources\UserResource\Pages;
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
        return $form->schema([
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
                Forms\Components\TextInput::make('points_balance')->numeric()
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('email')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('password')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('avatar')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('bio')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('enterprise_wechat_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\UserResource\Pages\ListUsers::route('/'),
            'create' => \App\Filament\Resources\UserResource\Pages\CreateUser::route('/create'),
            'edit' => \App\Filament\Resources\UserResource\Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
