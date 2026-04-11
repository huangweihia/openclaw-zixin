<?php

namespace App\Filament\Resources;

use App\Models\AuditLog;
use App\Filament\Resources\AuditLogResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class AuditLogResource extends BaseAdminResource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = '运营与自动化';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = '审计日志';

    protected static ?string $pluralModelLabel = '审计日志';


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
                Forms\Components\TextInput::make('action')->maxLength(65535),
                Forms\Components\TextInput::make('model_type')->maxLength(65535),
                Forms\Components\TextInput::make('model_id')->numeric(),
                Forms\Components\Textarea::make('old_values')->columnSpanFull()->helperText('JSON 数组，可手填'),
                Forms\Components\Textarea::make('new_values')->columnSpanFull()->helperText('JSON 数组，可手填'),
                Forms\Components\TextInput::make('ip')->maxLength(65535),
                Forms\Components\TextInput::make('user_agent')->maxLength(65535)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('action')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('model_type')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('model_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('old_values')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('new_values')->limit(40)->toggleable(),
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
                Infolists\Components\TextEntry::make('action')->columnSpanFull(),
                Infolists\Components\TextEntry::make('model_type')->columnSpanFull(),
                Infolists\Components\TextEntry::make('model_id')->columnSpanFull(),
                Infolists\Components\TextEntry::make('old_values')->columnSpanFull(),
                Infolists\Components\TextEntry::make('new_values')->columnSpanFull(),
                Infolists\Components\TextEntry::make('ip')->columnSpanFull(),
                Infolists\Components\TextEntry::make('user_agent')->columnSpanFull(),
                Infolists\Components\TextEntry::make('created_at')->columnSpanFull(),
                Infolists\Components\TextEntry::make('updated_at')->columnSpanFull()
        ]);
    }
    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\AuditLogResource\Pages\ListAuditLogs::route('/'),
            'view' => \App\Filament\Resources\AuditLogResource\Pages\ViewAuditLog::route('/{record}'),
        ];
    }
}
