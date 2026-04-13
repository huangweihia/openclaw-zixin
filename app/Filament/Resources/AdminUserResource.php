<?php

namespace App\Filament\Resources;

use App\Models\AdminRole;
use App\Models\AdminUser;
use App\Filament\Resources\AdminUserResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdminUserResource extends BaseAdminResource
{
    protected static ?string $model = AdminUser::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = '系统';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = '后台用户档案';

    protected static ?string $pluralModelLabel = '后台用户档案';


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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->relationship(
                    name: 'user',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn (Builder $query) => $query->where('role', 'admin'),
                )
                ->searchable()
                ->preload()
                ->required()
                ->helperText('须选择 role=admin 的站内账号。'),
            Forms\Components\TextInput::make('display_name')->maxLength(255),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\Toggle::make('is_super'),
            Forms\Components\CheckboxList::make('admin_role_ids')
                ->label('后台角色')
                ->options(fn () => AdminRole::query()->orderBy('name')->pluck('name', 'id'))
                ->columns(2)
                ->dehydrated(false)
                ->visibleOn('edit')
                ->helperText('保存档案后会同步到该用户的角色关联。'),
            Forms\Components\DateTimePicker::make('last_login_at')->disabled()->dehydrated(false),
            Forms\Components\TextInput::make('last_login_ip')->maxLength(45)->disabled()->dehydrated(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('登录账号')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('display_name')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('is_active')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('is_super')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('last_login_at')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('last_login_ip')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)
            ]))
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
            'index' => \App\Filament\Resources\AdminUserResource\Pages\ListAdminUsers::route('/'),
            'create' => \App\Filament\Resources\AdminUserResource\Pages\CreateAdminUser::route('/create'),
            'edit' => \App\Filament\Resources\AdminUserResource\Pages\EditAdminUser::route('/{record}/edit'),
        ];
    }
}
