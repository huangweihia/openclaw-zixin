<?php

namespace App\Filament\Resources;

use App\Models\Comment;
use App\Filament\Resources\CommentResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class CommentResource extends BaseAdminResource
{
    protected static ?string $model = Comment::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = '内容与社区';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = '评论';

    protected static ?string $pluralModelLabel = '评论';


    public static function canCreate(): bool
    {
        return static::canViewAny() && false;
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
            Forms\Components\TextInput::make('user_id')->numeric(),
                Forms\Components\TextInput::make('commentable_type')->maxLength(65535),
                Forms\Components\TextInput::make('commentable_id')->numeric(),
                Forms\Components\TextInput::make('parent_id')->numeric(),
                Forms\Components\Textarea::make('content')->columnSpanFull()->rows(6),
                Forms\Components\Toggle::make('is_hidden'),
                Forms\Components\TextInput::make('like_count')->numeric()
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('commentable_type')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('commentable_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('parent_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('content')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('is_hidden')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\CommentResource\Pages\ListComments::route('/'),
            'edit' => \App\Filament\Resources\CommentResource\Pages\EditComment::route('/{record}/edit'),
        ];
    }
}
