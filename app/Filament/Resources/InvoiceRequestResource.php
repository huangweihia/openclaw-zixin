<?php

namespace App\Filament\Resources;

use App\Models\InvoiceRequest;
use App\Filament\Resources\InvoiceRequestResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
class InvoiceRequestResource extends BaseAdminResource
{
    protected static ?string $model = InvoiceRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = '订单与财务';

    protected static ?int $navigationSort = 60;

    protected static ?string $modelLabel = '发票申请';

    protected static ?string $pluralModelLabel = '发票申请';


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
                Forms\Components\TextInput::make('invoice_type')->maxLength(65535),
                Forms\Components\TextInput::make('company_name')->maxLength(65535),
                Forms\Components\TextInput::make('tax_id')->numeric(),
                Forms\Components\TextInput::make('email')->maxLength(65535),
                Forms\Components\TextInput::make('status')->maxLength(65535),
                Forms\Components\TextInput::make('invoice_file')->maxLength(65535),
                Forms\Components\Textarea::make('admin_note')->columnSpanFull()->rows(6),
                Forms\Components\DateTimePicker::make('processed_at')
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('order_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('invoice_type')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('company_name')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('tax_id')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('email')->limit(40)->toggleable(),
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
            'index' => \App\Filament\Resources\InvoiceRequestResource\Pages\ListInvoiceRequests::route('/'),
            'edit' => \App\Filament\Resources\InvoiceRequestResource\Pages\EditInvoiceRequest::route('/{record}/edit'),
        ];
    }
}
