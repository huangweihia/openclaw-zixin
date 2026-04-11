<?php

namespace App\Filament\Resources;

use App\Models\UserSkin;
use App\Filament\Resources\UserSkinResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
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
        return static::canViewAny() && false;
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
            Forms\Components\TextInput::make('user_id')->numeric(),
                Forms\Components\TextInput::make('skin_id')->numeric(),
                Forms\Components\DateTimePicker::make('activated_at')
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('skin_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('activated_at')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\UserSkinResource\Pages\ListUserSkins::route('/'),
            'edit' => \App\Filament\Resources\UserSkinResource\Pages\EditUserSkin::route('/{record}/edit'),
        ];
    }
}
