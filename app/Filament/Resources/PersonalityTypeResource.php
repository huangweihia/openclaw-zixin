<?php

namespace App\Filament\Resources;

use App\Models\PersonalityType;
use App\Filament\Resources\PersonalityTypeResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class PersonalityTypeResource extends BaseAdminResource
{
    protected static ?string $model = PersonalityType::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = '人格测试';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = '人格类型';

    protected static ?string $pluralModelLabel = '人格类型';


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
            Forms\Components\TextInput::make('code')->maxLength(65535),
                Forms\Components\TextInput::make('cn_name')->maxLength(65535),
                Forms\Components\TextInput::make('intro')->maxLength(65535),
                Forms\Components\Textarea::make('description')->columnSpanFull()->rows(6),
                Forms\Components\TextInput::make('image_url')->maxLength(65535),
                Forms\Components\TextInput::make('pattern')->maxLength(65535),
                Forms\Components\Toggle::make('is_fallback'),
                Forms\Components\Toggle::make('is_active'),
                Forms\Components\TextInput::make('sort_order')->maxLength(65535)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('code')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('cn_name')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('intro')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('description')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('image_url')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('pattern')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\PersonalityTypeResource\Pages\ListPersonalityTypes::route('/'),
            'create' => \App\Filament\Resources\PersonalityTypeResource\Pages\CreatePersonalityType::route('/create'),
            'edit' => \App\Filament\Resources\PersonalityTypeResource\Pages\EditPersonalityType::route('/{record}/edit'),
        ];
    }
}
