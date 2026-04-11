<?php

namespace App\Filament\Resources;

use App\Models\Point;
use App\Filament\Resources\PointResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class PointResource extends BaseAdminResource
{
    protected static ?string $model = Point::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = '用户与会员';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = '积分流水';

    protected static ?string $pluralModelLabel = '积分流水';


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
                Forms\Components\TextInput::make('amount')->numeric()->step(0.01),
                Forms\Components\TextInput::make('balance')->maxLength(65535),
                Forms\Components\TextInput::make('type')->maxLength(65535),
                Forms\Components\TextInput::make('category')->maxLength(65535),
                Forms\Components\Textarea::make('description')->columnSpanFull()->rows(6),
                Forms\Components\TextInput::make('reference_type')->maxLength(65535),
                Forms\Components\TextInput::make('reference_id')->numeric(),
                Forms\Components\DateTimePicker::make('created_at')
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('amount')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('balance')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('type')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('category')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('description')->limit(40)->toggleable(),
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
                Infolists\Components\TextEntry::make('amount')->columnSpanFull(),
                Infolists\Components\TextEntry::make('balance')->columnSpanFull(),
                Infolists\Components\TextEntry::make('type')->columnSpanFull(),
                Infolists\Components\TextEntry::make('category')->columnSpanFull(),
                Infolists\Components\TextEntry::make('description')->columnSpanFull(),
                Infolists\Components\TextEntry::make('reference_type')->columnSpanFull(),
                Infolists\Components\TextEntry::make('reference_id')->columnSpanFull(),
                Infolists\Components\TextEntry::make('created_at')->columnSpanFull(),
                Infolists\Components\TextEntry::make('updated_at')->columnSpanFull()
        ]);
    }
    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\PointResource\Pages\ListPoints::route('/'),
            'view' => \App\Filament\Resources\PointResource\Pages\ViewPoint::route('/{record}'),
        ];
    }
}
