<?php

namespace App\Filament\Resources;

use App\Models\AdminNavItem;
use App\Models\AdminRole;
use App\Filament\Resources\AdminRoleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Forms\Get;
use Filament\Tables;
use Filament\Tables\Table;
class AdminRoleResource extends BaseAdminResource
{
    protected static ?string $model = AdminRole::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationGroup = '系统';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = '后台角色';

    protected static ?string $pluralModelLabel = '后台角色';


    public static function canCreate(): bool
    {
        return static::canViewAny() && true;
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny() && true;
    }

    public static function canDelete($record): bool
    {
        return static::canViewAny() && true;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(128),
            Forms\Components\TextInput::make('key')
                ->required()
                ->maxLength(64)
                ->unique(AdminRole::class, 'key', ignoreRecord: true)
                ->helperText('英文标识，创建后勿随意修改'),
            Forms\Components\Textarea::make('description')
                ->columnSpanFull()
                ->rows(3)
                ->maxLength(500),
            Forms\Components\Select::make('menu_mode')
                ->options([
                    'inherit' => '继承：具备权限的菜单均可显示',
                    'whitelist' => '白名单：仅下方列表中的菜单项',
                ])
                ->required()
                ->native(false),
            Forms\Components\CheckboxList::make('permissions')
                ->relationship('permissions', 'key')
                ->searchable()
                ->columns(2)
                ->columnSpanFull()
                ->helperText('勾选该角色拥有的权限键（含各菜单 read / 管理 write 等）。'),
            Forms\Components\Repeater::make('menuItems')
                ->relationship()
                ->visible(fn (Get $get): bool => ($get('menu_mode') ?? 'inherit') === 'whitelist')
                ->schema([
                    Forms\Components\Select::make('menu_key')
                        ->label('菜单项')
                        ->options(fn () => AdminNavItem::query()->orderBy('menu_key')->pluck('label', 'menu_key'))
                        ->required()
                        ->searchable(),
                    Forms\Components\TextInput::make('sort_order')
                        ->numeric()
                        ->default(0),
                ])
                ->columnSpanFull()
                ->collapsible()
                ->defaultItems(0)
                ->addActionLabel('添加菜单项'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('key')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('description')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('menu_mode')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\AdminRoleResource\Pages\ListAdminRoles::route('/'),
            'create' => \App\Filament\Resources\AdminRoleResource\Pages\CreateAdminRole::route('/create'),
            'edit' => \App\Filament\Resources\AdminRoleResource\Pages\EditAdminRole::route('/{record}/edit'),
        ];
    }
}
