<?php

namespace App\Filament\Resources;

use App\Models\PushNotification;
use App\Filament\Resources\PushNotificationResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PushNotificationResource extends BaseAdminResource
{
    protected static ?string $model = PushNotification::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationGroup = '营销与触达';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = '推送记录';

    protected static ?string $pluralModelLabel = '推送记录';


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
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('title')->maxLength(65535),
                Forms\Components\Textarea::make('content')->columnSpanFull()->rows(6),
                Forms\Components\TextInput::make('action_url')->maxLength(65535),
                Forms\Components\Textarea::make('data')->columnSpanFull()->helperText('JSON 数组，可手填'),
                Forms\Components\Toggle::make('is_sent'),
                Forms\Components\Toggle::make('is_read'),
                Forms\Components\DateTimePicker::make('sent_at'),
                Forms\Components\DateTimePicker::make('read_at')
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('用户')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('title')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('content')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('action_url')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('data')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('is_sent')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\PushNotificationResource\Pages\ListPushNotifications::route('/'),
            'create' => \App\Filament\Resources\PushNotificationResource\Pages\CreatePushNotification::route('/create'),
            'edit' => \App\Filament\Resources\PushNotificationResource\Pages\EditPushNotification::route('/{record}/edit'),
        ];
    }
}
