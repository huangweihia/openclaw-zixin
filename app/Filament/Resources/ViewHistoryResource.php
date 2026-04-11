<?php

namespace App\Filament\Resources;

use App\Models\ViewHistory;
use App\Filament\Resources\ViewHistoryResource\Pages;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewHistoryResource extends BaseAdminResource
{
    protected static ?string $model = ViewHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-eye';

    protected static ?string $navigationGroup = '用户与会员';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = '浏览记录';

    protected static ?string $pluralModelLabel = '浏览记录';

    public static function canCreate(): bool
    {
        return static::canViewAny() && false;
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny() && false;
    }

    public static function canDelete($record): bool
    {
        return static::canViewAny() && false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('user_id')->numeric(),
            Forms\Components\TextInput::make('viewable_type')->maxLength(65535),
            Forms\Components\TextInput::make('viewable_id')->numeric(),
            Forms\Components\DateTimePicker::make('viewed_at'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('用户')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('viewable_type')
                    ->label('类型')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('viewable_id')->label('对象 ID')->toggleable(),
                Tables\Columns\TextColumn::make('viewed_at')->dateTime()->sortable()->toggleable(),
            ])
            ->defaultSort('viewed_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('浏览信息')
                ->icon('heroicon-o-eye')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('id')->label('记录 ID'),
                    Infolists\Components\TextEntry::make('user.name')
                        ->label('用户')
                        ->placeholder('未登录 / 无'),
                    Infolists\Components\TextEntry::make('viewable_type')
                        ->label('资源类型')
                        ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—')
                        ->description(fn (ViewHistory $record): string => 'Morph：'.((string) ($record->viewable_type ?? ''))),
                    Infolists\Components\TextEntry::make('viewable_id')->label('资源 ID'),
                    Infolists\Components\TextEntry::make('viewed_at')->label('浏览时间')->dateTime()->columnSpanFull(),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\ViewHistoryResource\Pages\ListViewHistories::route('/'),
            'view' => \App\Filament\Resources\ViewHistoryResource\Pages\ViewViewHistory::route('/{record}'),
        ];
    }
}
