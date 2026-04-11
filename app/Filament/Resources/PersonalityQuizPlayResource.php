<?php

namespace App\Filament\Resources;

use App\Models\PersonalityQuizPlay;
use App\Filament\Resources\PersonalityQuizPlayResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class PersonalityQuizPlayResource extends BaseAdminResource
{
    protected static ?string $model = PersonalityQuizPlay::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = '人格测试';

    protected static ?int $navigationSort = 60;

    protected static ?string $modelLabel = '测评记录';

    protected static ?string $pluralModelLabel = '测评记录';


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
            Forms\Components\TextInput::make('guest_token')->maxLength(65535),
                Forms\Components\TextInput::make('user_id')->numeric(),
                Forms\Components\DateTimePicker::make('completed_at')
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('guest_token')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('user_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('completed_at')->limit(40)->toggleable(),
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
                Infolists\Components\TextEntry::make('guest_token')->columnSpanFull(),
                Infolists\Components\TextEntry::make('user_id')->columnSpanFull(),
                Infolists\Components\TextEntry::make('completed_at')->columnSpanFull(),
                Infolists\Components\TextEntry::make('created_at')->columnSpanFull(),
                Infolists\Components\TextEntry::make('updated_at')->columnSpanFull()
        ]);
    }
    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\PersonalityQuizPlayResource\Pages\ListPersonalityQuizPlays::route('/'),
            'view' => \App\Filament\Resources\PersonalityQuizPlayResource\Pages\ViewPersonalityQuizPlay::route('/{record}'),
        ];
    }
}
