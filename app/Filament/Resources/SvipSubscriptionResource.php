<?php

namespace App\Filament\Resources;

use App\Models\SvipSubscription;
use App\Filament\Resources\SvipSubscriptionResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SvipSubscriptionResource extends BaseAdminResource
{
    protected static ?string $model = SvipSubscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = '订单与财务';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'SVIP 订阅';

    protected static ?string $pluralModelLabel = 'SVIP 订阅';


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
            Forms\Components\TextInput::make('user_id')->numeric(),
                Forms\Components\TextInput::make('name')->maxLength(65535),
                Forms\Components\Textarea::make('keywords')->columnSpanFull()->rows(6),
                Forms\Components\Textarea::make('exclude_keywords')->columnSpanFull()->rows(6),
                Forms\Components\Textarea::make('sources')->columnSpanFull()->rows(6),
                Forms\Components\TextInput::make('frequency')->maxLength(65535),
                Forms\Components\Textarea::make('push_methods')->columnSpanFull()->rows(6),
                Forms\Components\Toggle::make('is_active'),
                Forms\Components\DateTimePicker::make('last_fetch_at'),
                Forms\Components\TextInput::make('last_fetch_count')->numeric()
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('用户')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('name')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('keywords')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('exclude_keywords')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('sources')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('frequency')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\SvipSubscriptionResource\Pages\ListSvipSubscriptions::route('/'),
            'create' => \App\Filament\Resources\SvipSubscriptionResource\Pages\CreateSvipSubscription::route('/create'),
            'edit' => \App\Filament\Resources\SvipSubscriptionResource\Pages\EditSvipSubscription::route('/{record}/edit'),
        ];
    }
}
