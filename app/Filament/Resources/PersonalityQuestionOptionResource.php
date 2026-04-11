<?php

namespace App\Filament\Resources;

use App\Models\PersonalityQuestionOption;
use App\Filament\Resources\PersonalityQuestionOptionResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class PersonalityQuestionOptionResource extends BaseAdminResource
{
    protected static ?string $model = PersonalityQuestionOption::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = '人格测试';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = '题目选项';

    protected static ?string $pluralModelLabel = '题目选项';


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
            Forms\Components\TextInput::make('personality_question_id')->numeric(),
                Forms\Components\TextInput::make('label')->maxLength(65535),
                Forms\Components\TextInput::make('value')->maxLength(65535),
                Forms\Components\TextInput::make('sort_order')->maxLength(65535)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('personality_question_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('label')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('value')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('sort_order')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\PersonalityQuestionOptionResource\Pages\ListPersonalityQuestionOptions::route('/'),
            'create' => \App\Filament\Resources\PersonalityQuestionOptionResource\Pages\CreatePersonalityQuestionOption::route('/create'),
            'edit' => \App\Filament\Resources\PersonalityQuestionOptionResource\Pages\EditPersonalityQuestionOption::route('/{record}/edit'),
        ];
    }
}
