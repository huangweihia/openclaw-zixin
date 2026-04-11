<?php

namespace App\Filament\Resources;

use App\Models\Order;
use App\Filament\Resources\OrderResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class OrderResource extends BaseAdminResource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = '订单与财务';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = '订单';

    protected static ?string $pluralModelLabel = '订单';


    public static function canCreate(): bool
    {
        return static::canViewAny() && false;
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
            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->nullable(),
            Forms\Components\TextInput::make('order_no')->maxLength(65535),
            Forms\Components\TextInput::make('product_type')->maxLength(65535),
            Forms\Components\TextInput::make('product_id')->numeric(),
            Forms\Components\TextInput::make('amount')->numeric()->step(0.01),
            Forms\Components\TextInput::make('status')->maxLength(65535),
            Forms\Components\TextInput::make('payment_id')->numeric(),
            Forms\Components\TextInput::make('payment_method')->maxLength(65535),
            Forms\Components\DateTimePicker::make('paid_at'),
            Forms\Components\TextInput::make('remark')->maxLength(65535),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('用户')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('order_no')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('product_type')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('product_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('amount')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('status')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\OrderResource\Pages\ListOrders::route('/'),
            'edit' => \App\Filament\Resources\OrderResource\Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
