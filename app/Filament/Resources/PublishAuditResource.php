<?php

namespace App\Filament\Resources;

use App\Models\PublishAudit;
use App\Filament\Resources\PublishAuditResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PublishAuditResource extends BaseAdminResource
{
    protected static ?string $model = PublishAudit::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = '内容与社区';

    protected static ?int $navigationSort = 70;

    protected static ?string $modelLabel = '发布审计';

    protected static ?string $pluralModelLabel = '发布审计';

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
        return parent::getEloquentQuery()
            ->with(['userPost', 'user', 'auditor']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('publish_id')
                ->label('关联动态')
                ->relationship('userPost', 'title')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('user_id')
                ->label('用户')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('auditor_id')
                ->label('审核人')
                ->relationship('auditor', 'name')
                ->searchable()
                ->preload()
                ->nullable(),
            Forms\Components\Select::make('status')
                ->options([
                    'pending' => '待审核',
                    'approved' => '已通过',
                    'rejected' => '已驳回',
                ])
                ->required(),
            Forms\Components\Textarea::make('reject_reason')
                ->label('驳回原因')
                ->rows(3)
                ->columnSpanFull(),
            Forms\Components\Textarea::make('suggest')
                ->label('修改建议')
                ->rows(3)
                ->columnSpanFull(),
            Forms\Components\TextInput::make('priority')
                ->label('优先级')
                ->numeric()
                ->default(0),
            Forms\Components\DateTimePicker::make('audited_at')
                ->label('审核时间'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('userPost.title')
                    ->label('动态标题')
                    ->limit(40)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('userPost', fn ($q) => $q->where('title', 'like', "%{$search}%"));
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('用户')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('auditor.name')
                    ->label('审核人')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('状态')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending' => '待审核',
                        'approved' => '已通过',
                        'rejected' => '已驳回',
                        default => (string) $state,
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('reject_reason')
                    ->label('驳回原因')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('suggest')
                    ->label('修改建议')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('priority')
                    ->label('优先级')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('audited_at')
                    ->label('审核时间')
                    ->dateTime()
                    ->sortable()
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
            'index' => Pages\ListPublishAudits::route('/'),
            'edit' => Pages\EditPublishAudit::route('/{record}/edit'),
        ];
    }
}
