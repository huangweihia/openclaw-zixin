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

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('personality_dimension_id')
                ->relationship('dimension', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Textarea::make('body')
                ->required()
                ->columnSpanFull()
                ->rows(4),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('dimension.name')
                    ->label('所属维度')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('body')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('sort_order')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('is_active')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\PersonalityQuestionResource\Pages\ListPersonalityQuestions::route('/'),
            'create' => \App\Filament\Resources\PersonalityQuestionResource\Pages\CreatePersonalityQuestion::route('/create'),
            'edit' => \App\Filament\Resources\PersonalityQuestionResource\Pages\EditPersonalityQuestion::route('/{record}/edit'),
        ];
    }
}
