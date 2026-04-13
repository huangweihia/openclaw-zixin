<?php

namespace App\Filament\Resources;

use App\Models\Project;
use App\Filament\Resources\ProjectResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class ProjectResource extends BaseAdminResource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = '内容与社区';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = '项目';

    protected static ?string $pluralModelLabel = '项目';


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
            Forms\Components\TextInput::make('name')->maxLength(65535),
                Forms\Components\TextInput::make('full_name')->maxLength(65535),
                Forms\Components\Textarea::make('description')->columnSpanFull()->rows(6),
                Forms\Components\TextInput::make('url')->maxLength(65535),
                Forms\Components\TextInput::make('language')->maxLength(65535),
                Forms\Components\TextInput::make('stars')->maxLength(65535),
                Forms\Components\TextInput::make('forks')->maxLength(65535),
                Forms\Components\TextInput::make('score')->numeric()->step(0.01),
                Forms\Components\Textarea::make('tags')->columnSpanFull()->helperText('JSON 数组，可手填'),
                Forms\Components\TextInput::make('monetization')->maxLength(65535),
                Forms\Components\TextInput::make('difficulty')->maxLength(65535),
                Forms\Components\Toggle::make('is_featured'),
                Forms\Components\Toggle::make('is_vip'),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('full_name')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('description')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('url')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('language')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('stars')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\ProjectResource\Pages\ListProjects::route('/'),
            'create' => \App\Filament\Resources\ProjectResource\Pages\CreateProject::route('/create'),
            'edit' => \App\Filament\Resources\ProjectResource\Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
