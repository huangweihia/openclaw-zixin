<?php

namespace App\Filament\Resources;

use App\Models\AdminPermission;
use App\Support\AdminPermissionModuleZh;
use App\Filament\Resources\AdminPermissionResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Tables;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

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
            Forms\Components\Textarea::make('description')->columnSpanFull()->rows(6),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('module')
                    ->label('模块')
                    ->formatStateUsing(fn (?string $state): string => AdminPermissionModuleZh::label($state))
                    ->description(fn (AdminPermission $record): string => (string) ($record->module ?? ''))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('action')
                    ->label('操作')
                    ->formatStateUsing(fn (?string $state): string => AdminPermissionModuleZh::actionLabel($state))
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('key')
                    ->label('权限键')
                    ->copyable()
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('description')
                    ->label('说明')
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->groups([
                Group::make('module')
                    ->label('按模块分组')
                    ->getTitleFromRecordUsing(fn (AdminPermission $record): string => AdminPermissionModuleZh::label($record->module))
                    ->collapsible(),
            ])
            ->defaultGroup('module')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('权限说明')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('id')->label('编号'),
                    Infolists\Components\TextEntry::make('key')
                        ->label('权限键')
                        ->copyable()
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('module')
                        ->label('模块')
                        ->formatStateUsing(function (?string $state, AdminPermission $record): string {
                            $zh = AdminPermissionModuleZh::label($state);
                            $slug = (string) ($record->module ?? '—');

                            return $zh.'（技术标识：'.$slug.'）';
                        }),
                    Infolists\Components\TextEntry::make('action')
                        ->label('操作代码')
                        ->badge()
                        ->formatStateUsing(fn (?string $state): string => AdminPermissionModuleZh::actionLabel($state)),
                    Infolists\Components\TextEntry::make('description')
                        ->label('说明')
                        ->columnSpanFull(),
                ]),
            Infolists\Components\Section::make('元数据')
                ->collapsed()
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('created_at')->label('创建时间')->dateTime(),
                    Infolists\Components\TextEntry::make('updated_at')->label('更新时间')->dateTime(),
                ]),
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
