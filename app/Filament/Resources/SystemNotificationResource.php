<?php

namespace App\Filament\Resources;

use App\Models\SystemNotification;
use App\Filament\Resources\SystemNotificationResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class SystemNotificationResource extends BaseAdminResource
{
    protected static ?string $model = SystemNotification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationGroup = '营销与触达';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = '系统通知';

    protected static ?string $pluralModelLabel = '系统通知';


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
            Forms\Components\TextInput::make('title')->maxLength(65535),
                Forms\Components\Textarea::make('content')->columnSpanFull()->rows(6),
                Forms\Components\TextInput::make('priority')->numeric(),
                Forms\Components\TextInput::make('type')->maxLength(65535),
                Forms\Components\TextInput::make('audience')->maxLength(65535),
                Forms\Components\TextInput::make('action_url')->maxLength(65535),
                Forms\Components\Toggle::make('is_published'),
                Forms\Components\DateTimePicker::make('expires_at'),
                Forms\Components\TextInput::make('created_by')->maxLength(65535)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('title')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('content')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('priority')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('type')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('audience')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('action_url')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\SystemNotificationResource\Pages\ListSystemNotifications::route('/'),
            'create' => \App\Filament\Resources\SystemNotificationResource\Pages\CreateSystemNotification::route('/create'),
            'edit' => \App\Filament\Resources\SystemNotificationResource\Pages\EditSystemNotification::route('/{record}/edit'),
        ];
    }
}
