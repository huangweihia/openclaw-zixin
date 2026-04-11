<?php

namespace App\Filament\Resources;

use App\Models\PersonalityQuestion;
use App\Filament\Resources\PersonalityQuestionResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class PersonalityQuestionResource extends BaseAdminResource
{
    protected static ?string $model = PersonalityQuestion::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = '人格测试';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = '测评题目';

    protected static ?string $pluralModelLabel = '测评题目';


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
            Forms\Components\TextInput::make('personality_dimension_id')->numeric(),
                Forms\Components\TextInput::make('body')->maxLength(65535),
                Forms\Components\TextInput::make('sort_order')->maxLength(65535),
                Forms\Components\Toggle::make('is_active')
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('personality_dimension_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('body')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\PersonalityQuestionResource\Pages\ListPersonalityQuestions::route('/'),
            'create' => \App\Filament\Resources\PersonalityQuestionResource\Pages\CreatePersonalityQuestion::route('/create'),
            'edit' => \App\Filament\Resources\PersonalityQuestionResource\Pages\EditPersonalityQuestion::route('/{record}/edit'),
        ];
    }
}
