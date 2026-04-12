<?php

namespace App\Filament\Resources;

use App\Models\PersonalityDimension;
use App\Filament\Resources\PersonalityDimensionResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class PersonalityDimensionResource extends BaseAdminResource
{
    protected static ?string $model = PersonalityDimension::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = '人格测试';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = '人格维度';

    protected static ?string $pluralModelLabel = '人格维度';


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
            Forms\Components\TextInput::make('code')->maxLength(65535),
                Forms\Components\TextInput::make('name')->maxLength(65535),
                Forms\Components\TextInput::make('model_group')->maxLength(65535),
                Forms\Components\TextInput::make('sort_order')->maxLength(65535),
                Forms\Components\TextInput::make('explanation_l')->maxLength(65535),
                Forms\Components\TextInput::make('explanation_m')->maxLength(65535),
                Forms\Components\TextInput::make('explanation_h')->maxLength(65535),
                Forms\Components\Toggle::make('is_active')
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('code')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('name')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('model_group')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('sort_order')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('explanation_l')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('explanation_m')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\PersonalityDimensionResource\Pages\ListPersonalityDimensions::route('/'),
            'create' => \App\Filament\Resources\PersonalityDimensionResource\Pages\CreatePersonalityDimension::route('/create'),
            'edit' => \App\Filament\Resources\PersonalityDimensionResource\Pages\EditPersonalityDimension::route('/{record}/edit'),
        ];
    }
}
