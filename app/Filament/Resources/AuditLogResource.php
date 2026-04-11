<?php

namespace App\Filament\Resources;

use App\Models\AuditLog;
use App\Support\FilamentJson;
use Illuminate\Database\Eloquent\Builder;
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user']);
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
            Forms\Components\TextInput::make('user_agent')->maxLength(65535),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('操作人')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('action')->limit(32)->badge()->toggleable(),
                Tables\Columns\TextColumn::make('model_type')
                    ->label('模型')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('model_id')->label('ID')->toggleable(),
                Tables\Columns\TextColumn::make('ip')->limit(24)->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('操作信息')
                ->icon('heroicon-o-shield-check')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('id')->label('编号'),
                    Infolists\Components\TextEntry::make('user.name')
                        ->label('操作人')
                        ->placeholder('—'),
                    Infolists\Components\TextEntry::make('action')
                        ->label('动作')
                        ->badge()
                        ->copyable(),
                    Infolists\Components\TextEntry::make('model_type')
                        ->label('模型类型')
                        ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—'),
                    Infolists\Components\TextEntry::make('model_id')->label('模型 ID'),
                    Infolists\Components\TextEntry::make('created_at')->label('操作时间')->dateTime(),
                ]),
            Infolists\Components\Section::make('变更内容')
                ->icon('heroicon-o-arrows-right-left')
                ->columns(1)
                ->schema([
                    Infolists\Components\TextEntry::make('old_values')
                        ->label('变更前')
                        ->formatStateUsing(fn ($state): string => FilamentJson::pretty($state))
                        ->columnSpanFull()
                        ->extraAttributes(['class' => 'font-mono text-xs whitespace-pre-wrap max-h-64 overflow-y-auto']),
                    Infolists\Components\TextEntry::make('new_values')
                        ->label('变更后')
                        ->formatStateUsing(fn ($state): string => FilamentJson::pretty($state))
                        ->columnSpanFull()
                        ->extraAttributes(['class' => 'font-mono text-xs whitespace-pre-wrap max-h-64 overflow-y-auto']),
                ])
                ->collapsible(),
            Infolists\Components\Section::make('请求环境')
                ->icon('heroicon-o-globe-alt')
                ->collapsed()
                ->schema([
                    Infolists\Components\TextEntry::make('ip')->label('IP')->copyable(),
                    Infolists\Components\TextEntry::make('user_agent')
                        ->label('User-Agent')
                        ->columnSpanFull()
                        ->prose(),
                ]),
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
