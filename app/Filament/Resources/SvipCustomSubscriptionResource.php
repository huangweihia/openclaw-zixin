<?php

namespace App\Filament\Resources;

use App\Models\SvipCustomSubscription;
use App\Filament\Resources\SvipCustomSubscriptionResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class SvipCustomSubscriptionResource extends BaseAdminResource
{
    protected static ?string $model = SvipCustomSubscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = '订单与财务';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = 'SVIP 定制';

    protected static ?string $pluralModelLabel = 'SVIP 定制';


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
            Forms\Components\TextInput::make('user_id')->numeric(),
                Forms\Components\TextInput::make('plan_name')->maxLength(65535),
                Forms\Components\Textarea::make('description')->columnSpanFull()->rows(6),
                Forms\Components\TextInput::make('delivery_frequency')->maxLength(65535),
                Forms\Components\TextInput::make('preferred_send_time')->maxLength(65535),
                Forms\Components\TextInput::make('delivery_channel')->maxLength(65535),
                Forms\Components\TextInput::make('amount')->numeric()->step(0.01),
                Forms\Components\TextInput::make('duration_days')->maxLength(65535),
                Forms\Components\Textarea::make('services')->columnSpanFull()->helperText('JSON 数组，可手填'),
                Forms\Components\TextInput::make('status')->maxLength(65535),
                Forms\Components\DateTimePicker::make('started_at'),
                Forms\Components\DateTimePicker::make('expires_at')
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('plan_name')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('description')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('delivery_frequency')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('preferred_send_time')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('delivery_channel')->limit(40)->toggleable(),
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
                Infolists\Components\TextEntry::make('user_id')->columnSpanFull(),
                Infolists\Components\TextEntry::make('plan_name')->columnSpanFull(),
                Infolists\Components\TextEntry::make('description')->columnSpanFull(),
                Infolists\Components\TextEntry::make('delivery_frequency')->columnSpanFull(),
                Infolists\Components\TextEntry::make('preferred_send_time')->columnSpanFull(),
                Infolists\Components\TextEntry::make('delivery_channel')->columnSpanFull(),
                Infolists\Components\TextEntry::make('amount')->columnSpanFull(),
                Infolists\Components\TextEntry::make('duration_days')->columnSpanFull(),
                Infolists\Components\TextEntry::make('services')->columnSpanFull(),
                Infolists\Components\TextEntry::make('status')->columnSpanFull(),
                Infolists\Components\TextEntry::make('started_at')->columnSpanFull(),
                Infolists\Components\TextEntry::make('expires_at')->columnSpanFull(),
                Infolists\Components\TextEntry::make('created_at')->columnSpanFull(),
                Infolists\Components\TextEntry::make('updated_at')->columnSpanFull()
        ]);
    }
    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\SvipCustomSubscriptionResource\Pages\ListSvipCustomSubscriptions::route('/'),
            'view' => \App\Filament\Resources\SvipCustomSubscriptionResource\Pages\ViewSvipCustomSubscription::route('/{record}'),
        ];
    }
}
