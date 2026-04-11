<?php

namespace App\Filament\Resources;

use App\Models\AdminPermission;
use App\Filament\Resources\AdminPermissionResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class AdminPermissionResource extends BaseAdminResource
{
    protected static ?string $model = AdminPermission::class;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    protected static ?string $navigationGroup = '系统';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = '权限字典';

    protected static ?string $pluralModelLabel = '权限字典';


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
            Forms\Components\TextInput::make('module')->maxLength(65535),
                Forms\Components\TextInput::make('action')->maxLength(65535),
                Forms\Components\TextInput::make('key')->maxLength(65535),
                Forms\Components\Textarea::make('description')->columnSpanFull()->rows(6)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('module')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('action')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('key')->limit(40)->toggleable(),
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
                Infolists\Components\TextEntry::make('module')->columnSpanFull(),
                Infolists\Components\TextEntry::make('action')->columnSpanFull(),
                Infolists\Components\TextEntry::make('key')->columnSpanFull(),
                Infolists\Components\TextEntry::make('description')->columnSpanFull(),
                Infolists\Components\TextEntry::make('created_at')->columnSpanFull(),
                Infolists\Components\TextEntry::make('updated_at')->columnSpanFull()
        ]);
    }
    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\AdminPermissionResource\Pages\ListAdminPermissions::route('/'),
            'view' => \App\Filament\Resources\AdminPermissionResource\Pages\ViewAdminPermission::route('/{record}'),
        ];
    }
}
