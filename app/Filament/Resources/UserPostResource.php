<?php

namespace App\Filament\Resources;

use App\Models\UserPost;
use App\Filament\Resources\UserPostResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserPostResource extends BaseAdminResource
{
    protected static ?string $model = UserPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = '内容与社区';

    protected static ?int $navigationSort = 60;

    protected static ?string $modelLabel = '用户动态';

    protected static ?string $pluralModelLabel = '用户动态';


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
        return parent::getEloquentQuery()->with('author');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->relationship('author', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('type')->maxLength(65535),
                Forms\Components\TextInput::make('title')->maxLength(65535),
                Forms\Components\Textarea::make('content')->columnSpanFull()->rows(6),
                Forms\Components\TextInput::make('category')->maxLength(65535),
                Forms\Components\Textarea::make('tags')->columnSpanFull()->helperText('JSON 数组，可手填'),
                Forms\Components\TextInput::make('cover_image')->maxLength(65535),
                Forms\Components\Textarea::make('attachments')->columnSpanFull()->helperText('JSON 数组，可手填'),
                Forms\Components\TextInput::make('visibility')->maxLength(65535),
                Forms\Components\TextInput::make('status')->maxLength(65535),
                Forms\Components\Textarea::make('audit_note')->columnSpanFull()->rows(6),
                Forms\Components\Select::make('audited_by')
                    ->relationship('auditor', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\DateTimePicker::make('audited_at'),
                Forms\Components\TextInput::make('view_count')->numeric(),
                Forms\Components\TextInput::make('like_count')->numeric(),
                Forms\Components\TextInput::make('comment_count')->numeric(),
                Forms\Components\TextInput::make('favorite_count')->numeric()
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('author.name')
                    ->label('作者')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('type')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('title')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('content')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('category')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('tags')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\UserPostResource\Pages\ListUserPosts::route('/'),
            'edit' => \App\Filament\Resources\UserPostResource\Pages\EditUserPost::route('/{record}/edit'),
        ];
    }
}
