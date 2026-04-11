<?php

namespace App\Filament\Resources;

use App\Models\RefundRequest;
use App\Filament\Resources\RefundRequestResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class RefundRequestResource extends BaseAdminResource
{
    protected static ?string $model = RefundRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static ?string $navigationGroup = '订单与财务';

    protected static ?int $navigationSort = 50;

    protected static ?string $modelLabel = '退款申请';

    protected static ?string $pluralModelLabel = '退款申请';


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
            Forms\Components\TextInput::make('user_id')->numeric(),
                Forms\Components\TextInput::make('order_id')->numeric(),
                Forms\Components\Textarea::make('reason')->columnSpanFull()->rows(6),
                Forms\Components\Textarea::make('description')->columnSpanFull()->rows(6),
                Forms\Components\TextInput::make('status')->maxLength(65535),
                Forms\Components\TextInput::make('refund_amount')->numeric()->step(0.01),
                Forms\Components\Textarea::make('admin_note')->columnSpanFull()->rows(6),
                Forms\Components\DateTimePicker::make('processed_at'),
                Forms\Components\TextInput::make('processed_by')->maxLength(65535)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('order_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('reason')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('description')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('status')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('refund_amount')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\RefundRequestResource\Pages\ListRefundRequests::route('/'),
            'edit' => \App\Filament\Resources\RefundRequestResource\Pages\EditRefundRequest::route('/{record}/edit'),
        ];
    }
}
