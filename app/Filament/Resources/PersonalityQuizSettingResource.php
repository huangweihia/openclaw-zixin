<?php

namespace App\Filament\Resources;

use App\Models\PersonalityQuizSetting;
use App\Filament\Resources\PersonalityQuizSettingResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class PersonalityQuizSettingResource extends BaseAdminResource
{
    protected static ?string $model = PersonalityQuizSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?string $navigationGroup = '人格测试';

    protected static ?int $navigationSort = 50;

    protected static ?string $modelLabel = '测评设置';

    protected static ?string $pluralModelLabel = '测评设置';


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
            'index' => \App\Filament\Resources\PersonalityQuizSettingResource\Pages\ListPersonalityQuizSettings::route('/'),
            'create' => \App\Filament\Resources\PersonalityQuizSettingResource\Pages\CreatePersonalityQuizSetting::route('/create'),
            'edit' => \App\Filament\Resources\PersonalityQuizSettingResource\Pages\EditPersonalityQuizSetting::route('/{record}/edit'),
        ];
    }
}
