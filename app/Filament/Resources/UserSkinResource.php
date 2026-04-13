<?php

namespace App\Filament\Resources;

use App\Models\UserSkin;
use App\Filament\Resources\UserSkinResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserSkinResource extends BaseAdminResource
{
    protected static ?string $model = UserSkin::class;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationGroup = '站点与外观';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = '用户皮肤';

    protected static ?string $pluralModelLabel = '用户皮肤';


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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'skinConfig']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('skin_id')
                ->relationship('skinConfig', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\DateTimePicker::make('activated_at')
                ->native(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('用户')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('skinConfig.name')
                    ->label('皮肤')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('activated_at')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)
            ]))
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
            'index' => \App\Filament\Resources\UserSkinResource\Pages\ListUserSkins::route('/'),
            'create' => \App\Filament\Resources\UserSkinResource\Pages\CreateUserSkin::route('/create'),
            'edit' => \App\Filament\Resources\UserSkinResource\Pages\EditUserSkin::route('/{record}/edit'),
        ];
    }
}
