<?php

namespace App\Filament\Resources;

use App\Models\ViewHistory;
use App\Filament\Resources\ViewHistoryResource\Pages;
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

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('user_id')->numeric(),
                Forms\Components\TextInput::make('viewable_type')->maxLength(65535),
                Forms\Components\TextInput::make('viewable_id')->numeric(),
                Forms\Components\DateTimePicker::make('viewed_at')
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('viewable_type')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('viewable_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('viewed_at')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
            ])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\TextEntry::make('id')->columnSpanFull(),
                Infolists\Components\TextEntry::make('user_id')->columnSpanFull(),
                Infolists\Components\TextEntry::make('viewable_type')->columnSpanFull(),
                Infolists\Components\TextEntry::make('viewable_id')->columnSpanFull(),
                Infolists\Components\TextEntry::make('viewed_at')->columnSpanFull(),
                Infolists\Components\TextEntry::make('created_at')->columnSpanFull(),
                Infolists\Components\TextEntry::make('updated_at')->columnSpanFull()
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
