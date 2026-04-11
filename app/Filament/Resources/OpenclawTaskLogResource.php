<?php

namespace App\Filament\Resources;

use App\Models\OpenclawTaskLog;
use App\Filament\Resources\OpenclawTaskLogResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class OpenclawTaskLogResource extends BaseAdminResource
{
    protected static ?string $model = OpenclawTaskLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-command-line';

    protected static ?string $navigationGroup = '运营与自动化';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'OpenClaw 日志';

    protected static ?string $pluralModelLabel = 'OpenClaw 日志';


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
        return static::canViewAny() && true;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('task_name')->maxLength(65535),
                Forms\Components\TextInput::make('task_id')->numeric(),
                Forms\Components\TextInput::make('task_type')->maxLength(65535),
                Forms\Components\TextInput::make('status')->maxLength(65535),
                Forms\Components\TextInput::make('duration_ms')->numeric(),
                Forms\Components\Textarea::make('data_summary')->columnSpanFull()->helperText('JSON 数组，可手填'),
                Forms\Components\TextInput::make('total_items')->numeric(),
                Forms\Components\TextInput::make('success_count')->numeric(),
                Forms\Components\TextInput::make('failed_count')->numeric(),
                Forms\Components\TextInput::make('skipped_count')->numeric(),
                Forms\Components\TextInput::make('api_endpoint')->maxLength(65535),
                Forms\Components\TextInput::make('push_status')->maxLength(65535),
                Forms\Components\TextInput::make('push_response')->maxLength(65535),
                Forms\Components\TextInput::make('error_message')->maxLength(65535),
                Forms\Components\TextInput::make('error_details')->maxLength(65535),
                Forms\Components\DateTimePicker::make('started_at'),
                Forms\Components\DateTimePicker::make('finished_at')
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('task_name')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('task_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('task_type')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('status')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('duration_ms')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('data_summary')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\TextEntry::make('id')->columnSpanFull(),
                Infolists\Components\TextEntry::make('task_name')->columnSpanFull(),
                Infolists\Components\TextEntry::make('task_id')->columnSpanFull(),
                Infolists\Components\TextEntry::make('task_type')->columnSpanFull(),
                Infolists\Components\TextEntry::make('status')->columnSpanFull(),
                Infolists\Components\TextEntry::make('duration_ms')->columnSpanFull(),
                Infolists\Components\TextEntry::make('data_summary')->columnSpanFull(),
                Infolists\Components\TextEntry::make('total_items')->columnSpanFull(),
                Infolists\Components\TextEntry::make('success_count')->columnSpanFull(),
                Infolists\Components\TextEntry::make('failed_count')->columnSpanFull(),
                Infolists\Components\TextEntry::make('skipped_count')->columnSpanFull(),
                Infolists\Components\TextEntry::make('api_endpoint')->columnSpanFull(),
                Infolists\Components\TextEntry::make('push_status')->columnSpanFull(),
                Infolists\Components\TextEntry::make('push_response')->columnSpanFull(),
                Infolists\Components\TextEntry::make('error_message')->columnSpanFull(),
                Infolists\Components\TextEntry::make('error_details')->columnSpanFull(),
                Infolists\Components\TextEntry::make('started_at')->columnSpanFull(),
                Infolists\Components\TextEntry::make('finished_at')->columnSpanFull(),
                Infolists\Components\TextEntry::make('created_at')->columnSpanFull(),
                Infolists\Components\TextEntry::make('updated_at')->columnSpanFull()
        ]);
    }
    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\OpenclawTaskLogResource\Pages\ListOpenclawTaskLogs::route('/'),
            'view' => \App\Filament\Resources\OpenclawTaskLogResource\Pages\ViewOpenclawTaskLog::route('/{record}'),
        ];
    }
}
