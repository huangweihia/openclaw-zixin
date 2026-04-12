<?php

namespace App\Filament\Resources;

use App\Models\Article;
use App\Filament\Resources\ArticleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ArticleResource extends BaseAdminResource
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = '内容与社区';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = '文章';

    protected static ?string $pluralModelLabel = '文章';


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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('category');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('category_id')
                ->relationship('category', 'name')
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('title')->maxLength(65535),
                Forms\Components\TextInput::make('slug')->maxLength(65535),
                Forms\Components\Textarea::make('summary')->columnSpanFull()->rows(6),
                Forms\Components\Textarea::make('content')->columnSpanFull()->rows(6),
                Forms\Components\TextInput::make('cover_image')->maxLength(65535),
                Forms\Components\Select::make('author_id')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('view_count')->numeric(),
                Forms\Components\TextInput::make('like_count')->numeric(),
                Forms\Components\Toggle::make('is_vip'),
                Forms\Components\Toggle::make('is_published'),
                Forms\Components\DateTimePicker::make('published_at'),
                Forms\Components\TextInput::make('source_url')->maxLength(65535),
                Forms\Components\TextInput::make('meta_keywords')->maxLength(65535),
                Forms\Components\TextInput::make('meta_description')->maxLength(65535)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('分类')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('title')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('slug')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('summary')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('content')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('cover_image')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\ArticleResource\Pages\ListArticles::route('/'),
            'create' => \App\Filament\Resources\ArticleResource\Pages\CreateArticle::route('/create'),
            'edit' => \App\Filament\Resources\ArticleResource\Pages\EditArticle::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteKeyName(): ?string
    {
        return 'id';
    }
}
