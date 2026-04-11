<?php

namespace App\Filament\Resources;

use App\Models\CommentReport;
use App\Filament\Resources\CommentReportResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class CommentReportResource extends BaseAdminResource
{
    protected static ?string $model = CommentReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = '内容与社区';

    protected static ?int $navigationSort = 50;

    protected static ?string $modelLabel = '评论举报';

    protected static ?string $pluralModelLabel = '评论举报';


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
                Forms\Components\TextInput::make('comment_id')->numeric(),
                Forms\Components\Textarea::make('reason')->columnSpanFull()->rows(6),
                Forms\Components\Textarea::make('description')->columnSpanFull()->rows(6),
                Forms\Components\TextInput::make('status')->maxLength(65535),
                Forms\Components\Textarea::make('admin_note')->columnSpanFull()->rows(6),
                Forms\Components\TextInput::make('processed_by')->maxLength(65535),
                Forms\Components\DateTimePicker::make('processed_at')
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('comment_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('reason')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('description')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('status')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('admin_note')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\CommentReportResource\Pages\ListCommentReports::route('/'),
            'edit' => \App\Filament\Resources\CommentReportResource\Pages\EditCommentReport::route('/{record}/edit'),
        ];
    }
}
