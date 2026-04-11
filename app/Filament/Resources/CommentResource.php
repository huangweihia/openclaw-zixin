<?php

namespace App\Filament\Resources;

use App\Models\Comment;
use App\Filament\Resources\CommentResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'commentable', 'parent']);
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
            Forms\Components\TextInput::make('like_count')->numeric(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('用户')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('comment_target')
                    ->label('评论对象')
                    ->getStateUsing(function (Comment $record): string {
                        $c = $record->commentable;
                        $type = match (class_basename((string) $record->commentable_type)) {
                            'Article' => '文章',
                            'UserPost' => '动态',
                            'Project' => '项目',
                            default => '资源',
                        };
                        if ($c === null) {
                            return $type.' #'.(string) $record->commentable_id;
                        }
                        $title = $c->title ?? $c->name ?? '';

                        return $title !== ''
                            ? $type.'：'.Str::limit((string) $title, 40)
                            : $type.' #'.(string) $record->commentable_id;
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('parent.content')
                    ->label('父评论摘要')
                    ->limit(24)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('content')
                    ->label('评论内容')
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_hidden')
                    ->label('已隐藏')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComments::route('/'),
            'edit' => Pages\EditComment::route('/{record}/edit'),
        ];
    }
}
