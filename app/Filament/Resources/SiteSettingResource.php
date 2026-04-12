<?php

namespace App\Filament\Resources;

use App\Models\SiteSetting;
use App\Filament\Resources\SiteSettingResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class SiteSettingResource extends BaseAdminResource
{
    protected static ?string $model = SiteSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationGroup = '站点与外观';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = '站点设置';

    protected static ?string $pluralModelLabel = '站点设置';


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

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('key')->maxLength(65535),
                Forms\Components\TextInput::make('value')->maxLength(65535)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('key')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('value')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\SiteSettingResource\Pages\ListSiteSettings::route('/'),
            'create' => \App\Filament\Resources\SiteSettingResource\Pages\CreateSiteSetting::route('/create'),
            'edit' => \App\Filament\Resources\SiteSettingResource\Pages\EditSiteSetting::route('/{record}/edit'),
        ];
    }
}
