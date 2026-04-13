<?php

namespace App\Filament\Resources;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Project;
use App\Models\UserPost;
use App\Filament\Resources\CommentResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CommentResource extends BaseAdminResource
{
    /** @return array<class-string, string> */
    public static function commentableTypeOptions(): array
    {
        return [
            Article::class => '文章',
            UserPost::class => '用户动态',
            Project::class => '项目',
        ];
    }

    /** @param  class-string  $type */
    public static function commentableTitleColumn(string $type): string
    {
        return $type === Project::class ? 'name' : 'title';
    }

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
            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('commentable_type')
                ->label('评论对象类型')
                ->options(static::commentableTypeOptions())
                ->required()
                ->native(false)
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('commentable_id', null)),
            Forms\Components\Select::make('commentable_id')
                ->label('评论对象')
                ->required()
                ->searchable()
                ->disabled(fn (Get $get): bool => blank($get('commentable_type')))
                ->getSearchResultsUsing(function (string $search, Get $get): array {
                    $type = $get('commentable_type');
                    if (! is_string($type) || $type === '' || ! class_exists($type) || ! isset(static::commentableTypeOptions()[$type])) {
                        return [];
                    }
                    $col = static::commentableTitleColumn($type);
                    $q = $type::query();
                    if ($type === Article::class) {
                        $q->where('is_published', true);
                    }
                    if ($type === UserPost::class) {
                        $q->where('status', 'approved');
                    }

                    return $q->where($col, 'like', '%'.$search.'%')
                        ->orderByDesc('id')
                        ->limit(50)
                        ->get()
                        ->mapWithKeys(function ($m) use ($col): array {
                            $t = (string) ($m->{$col} ?? '');

                            return [$m->id => '#'.$m->id.' '.Str::limit($t, 72)];
                        })
                        ->all();
                })
                ->getOptionLabelUsing(function ($value, Get $get): ?string {
                    if ($value === null || $value === '') {
                        return null;
                    }
                    $type = $get('commentable_type');
                    if (! is_string($type) || ! class_exists($type)) {
                        return '#'.(string) $value;
                    }
                    $m = $type::query()->find($value);
                    if ($m === null) {
                        return '#'.(string) $value;
                    }
                    $col = static::commentableTitleColumn($type);
                    $t = (string) ($m->{$col} ?? '');

                    return '#'.$m->id.' '.Str::limit($t, 72);
                }),
            Forms\Components\Select::make('parent_id')
                ->relationship('parent', 'content')
                ->getOptionLabelFromRecordUsing(
                    fn (Comment $c): string => '#'.$c->id.' '.Str::limit(strip_tags((string) $c->content), 48)
                )
                ->searchable()
                ->preload()
                ->nullable(),
            Forms\Components\Textarea::make('content')->columnSpanFull()->rows(6),
            Forms\Components\Toggle::make('is_hidden'),
            Forms\Components\TextInput::make('like_count')->numeric(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
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
            ]))
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
