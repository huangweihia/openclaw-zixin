<?php

namespace App\Filament\Resources;

use App\Models\EmailLog;
use App\Filament\Resources\EmailLogResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class EmailLogResource extends BaseAdminResource
{
    protected static ?string $model = EmailLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = '营销与触达';

    protected static ?int $navigationSort = 50;

    protected static ?string $modelLabel = '邮件记录';

    protected static ?string $pluralModelLabel = '邮件记录';


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
                Forms\Components\TextInput::make('template_key')->maxLength(65535),
                Forms\Components\TextInput::make('to')->maxLength(65535),
                Forms\Components\TextInput::make('subject')->maxLength(65535),
                Forms\Components\TextInput::make('status')->maxLength(65535),
                Forms\Components\TextInput::make('error_message')->maxLength(65535),
                Forms\Components\DateTimePicker::make('sent_at')
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('template_key')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('to')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('subject')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('status')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('error_message')->limit(40)->toggleable(),
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
                Infolists\Components\TextEntry::make('template_key')->columnSpanFull(),
                Infolists\Components\TextEntry::make('to')->columnSpanFull(),
                Infolists\Components\TextEntry::make('subject')->columnSpanFull(),
                Infolists\Components\TextEntry::make('status')->columnSpanFull(),
                Infolists\Components\TextEntry::make('error_message')->columnSpanFull(),
                Infolists\Components\TextEntry::make('sent_at')->columnSpanFull(),
                Infolists\Components\TextEntry::make('created_at')->columnSpanFull(),
                Infolists\Components\TextEntry::make('updated_at')->columnSpanFull()
        ]);
    }
    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\EmailLogResource\Pages\ListEmailLogs::route('/'),
            'view' => \App\Filament\Resources\EmailLogResource\Pages\ViewEmailLog::route('/{record}'),
        ];
    }
}
