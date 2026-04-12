<?php

namespace App\Filament\Resources;

use App\Models\AdminNavSection;
use App\Filament\Resources\AdminNavSectionResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class AdminNavSectionResource extends BaseAdminResource
{
    protected static ?string $model = AdminNavSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3-bottom-left';

    protected static ?string $navigationGroup = '系统';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = '导航分区';

    protected static ?string $pluralModelLabel = '导航分区';


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
            Forms\Components\TextInput::make('title')->maxLength(65535),
                Forms\Components\TextInput::make('sort_order')->maxLength(65535),
                Forms\Components\Toggle::make('is_active')
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('title')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('sort_order')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('is_active')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\AdminNavSectionResource\Pages\ListAdminNavSections::route('/'),
            'create' => \App\Filament\Resources\AdminNavSectionResource\Pages\CreateAdminNavSection::route('/create'),
            'edit' => \App\Filament\Resources\AdminNavSectionResource\Pages\EditAdminNavSection::route('/{record}/edit'),
        ];
    }
}
