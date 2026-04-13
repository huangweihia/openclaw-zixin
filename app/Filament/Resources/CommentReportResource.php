<?php

namespace App\Filament\Resources;

use App\Models\CommentReport;
use App\Filament\Resources\CommentReportResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CommentReportResource extends BaseAdminResource
{
    protected static ?string $model = CommentReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = '内容与社区';

    protected static ?int $navigationSort = 50;

    protected static ?string $modelLabel = '评论举报';

    protected static ?string $pluralModelLabel = '评论举报';

    /** @return array<string, string> */
    public static function reasonOptions(): array
    {
        return [
            'spam' => '垃圾信息',
            'abuse' => '辱骂',
            'harassment' => '骚扰',
            'other' => '其他',
        ];
    }

    /** @return array<string, string> */
    public static function statusOptions(): array
    {
        return [
            'pending' => '待处理',
            'processed' => '已处理',
            'rejected' => '已驳回',
        ];
    }

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
        return parent::getEloquentQuery()->with(['user', 'comment', 'processor']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('reporter')
                ->label('举报人')
                ->content(fn (?CommentReport $record): string => $record?->user?->name ?? '—'),
            Forms\Components\Placeholder::make('comment_excerpt')
                ->label('被举报评论')
                ->content(function (?CommentReport $record): string {
                    $c = $record?->comment;

                    return $c ? ('#'.$c->id.' '.\Illuminate\Support\Str::limit(strip_tags((string) $c->content), 200)) : '—';
                })
                ->columnSpanFull(),
            Forms\Components\Select::make('reason')
                ->label('原因')
                ->options(static::reasonOptions())
                ->required()
                ->native(false),
            Forms\Components\Textarea::make('description')
                ->label('说明')
                ->columnSpanFull()
                ->rows(4),
            Forms\Components\Select::make('status')
                ->label('状态')
                ->options(static::statusOptions())
                ->required()
                ->native(false),
            Forms\Components\Textarea::make('admin_note')
                ->label('管理员备注')
                ->columnSpanFull()
                ->rows(4),
            Forms\Components\Select::make('processed_by')
                ->label('处理人')
                ->relationship('processor', 'name')
                ->searchable()
                ->preload()
                ->nullable(),
            Forms\Components\DateTimePicker::make('processed_at')
                ->label('处理时间'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('举报人')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('comment.content')
                    ->label('被举报评论')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('reason')
                    ->label('原因')
                    ->formatStateUsing(fn (?string $state): string => static::reasonOptions()[$state ?? ''] ?? (string) $state)
                    ->badge()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('说明')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->label('状态')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => static::statusOptions()[$state ?? ''] ?? (string) $state)
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processed' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('processor.name')
                    ->label('处理人')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListCommentReports::route('/'),
            'edit' => Pages\EditCommentReport::route('/{record}/edit'),
        ];
    }
}
