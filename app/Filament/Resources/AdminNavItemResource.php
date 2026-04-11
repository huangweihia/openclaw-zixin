<?php

namespace App\Filament\Resources;

use App\Models\AdminNavItem;
use App\Filament\Resources\AdminNavItemResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class AdminNavItemResource extends BaseAdminResource
{
    protected static ?string $model = AdminNavItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationGroup = '系统';

    protected static ?int $navigationSort = 50;

    protected static ?string $modelLabel = '导航项';

    protected static ?string $pluralModelLabel = '导航项';


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
            Forms\Components\Select::make('admin_nav_section_id')
                ->relationship('section', 'title')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('menu_key')
                ->required()
                ->maxLength(128)
                ->unique(AdminNavItem::class, 'menu_key', ignoreRecord: true)
                ->helperText('唯一键，与 Filament 路由映射一致（如 articles、dashboard）。'),
            Forms\Components\TextInput::make('label')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('path')
                ->maxLength(512)
                ->helperText('旧版 Vue 路径，仅供参考；Filament 侧以 menu_key 映射为准。'),
            Forms\Components\TextInput::make('external_url')
                ->maxLength(1024)
                ->nullable(),
            Forms\Components\TextInput::make('icon')
                ->maxLength(64)
                ->helperText('可为 Element 图标名或留空；侧栏仍用 Resource 默认图标。'),
            Forms\Components\TextInput::make('perm_key')
                ->required()
                ->maxLength(128)
                ->helperText('对应 admin_permissions.key，用于 RBAC。'),
            Forms\Components\TextInput::make('sort_order')
                ->numeric()
                ->default(0),
            Forms\Components\Toggle::make('match_exact')
                ->helperText('路由是否精确匹配（旧版 SPA 用）。'),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('admin_nav_section_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('menu_key')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('label')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('path')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('external_url')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('icon')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('perm_key')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\AdminNavItemResource\Pages\ListAdminNavItems::route('/'),
            'create' => \App\Filament\Resources\AdminNavItemResource\Pages\CreateAdminNavItem::route('/create'),
            'edit' => \App\Filament\Resources\AdminNavItemResource\Pages\EditAdminNavItem::route('/{record}/edit'),
        ];
    }
}
