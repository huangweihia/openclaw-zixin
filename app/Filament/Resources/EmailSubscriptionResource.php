<?php

namespace App\Filament\Resources;

use App\Models\EmailSubscription;
use App\Filament\Resources\EmailSubscriptionResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmailSubscriptionResource extends BaseAdminResource
{
    protected static ?string $model = EmailSubscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = '营销与触达';

    protected static ?int $navigationSort = 60;

    protected static ?string $modelLabel = '邮件订阅';

    protected static ?string $pluralModelLabel = '邮件订阅';


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
                ->preload(),
            Forms\Components\TextInput::make('email')->maxLength(65535),
                Forms\Components\Textarea::make('subscribed_to')->columnSpanFull()->helperText('JSON 数组，可手填'),
                Forms\Components\Textarea::make('topic_schedule')->columnSpanFull()->helperText('JSON 数组，可手填'),
                Forms\Components\Toggle::make('is_unsubscribed'),
                Forms\Components\DateTimePicker::make('unsubscribed_at'),
                Forms\Components\TextInput::make('unsubscribe_token')->maxLength(65535)
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
                Tables\Columns\TextColumn::make('email')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('subscribed_to')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('topic_schedule')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('is_unsubscribed')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('unsubscribed_at')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\EmailSubscriptionResource\Pages\ListEmailSubscriptions::route('/'),
            'create' => \App\Filament\Resources\EmailSubscriptionResource\Pages\CreateEmailSubscription::route('/create'),
            'edit' => \App\Filament\Resources\EmailSubscriptionResource\Pages\EditEmailSubscription::route('/{record}/edit'),
        ];
    }
}
