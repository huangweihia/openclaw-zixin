<?php

namespace App\Filament\Resources;

use App\Models\InvoiceRequest;
use App\Filament\Resources\InvoiceRequestResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'order']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label('用户')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->disabled(fn (?InvoiceRequest $record): bool => $record !== null),
            Forms\Components\Select::make('order_id')
                ->label('订单')
                ->relationship('order', 'order_no')
                ->searchable()
                ->preload()
                ->required()
                ->disabled(fn (?InvoiceRequest $record): bool => $record !== null),
            Forms\Components\TextInput::make('invoice_type')
                ->label('发票类型')
                ->maxLength(65535),
            Forms\Components\TextInput::make('company_name')
                ->label('抬头/公司名')
                ->maxLength(65535),
            Forms\Components\TextInput::make('tax_id')
                ->label('税号')
                ->maxLength(64),
            Forms\Components\TextInput::make('email')
                ->label('接收邮箱')
                ->email()
                ->maxLength(65535),
            Forms\Components\TextInput::make('status')
                ->label('状态')
                ->maxLength(65535),
            Forms\Components\TextInput::make('invoice_file')
                ->label('发票文件')
                ->maxLength(65535),
            Forms\Components\Textarea::make('admin_note')
                ->label('管理员备注')
                ->columnSpanFull()
                ->rows(4),
            Forms\Components\DateTimePicker::make('processed_at')
                ->label('处理时间'),
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
                Tables\Columns\TextColumn::make('order.order_no')
                    ->label('订单号')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('invoice_type')
                    ->label('发票类型')
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('company_name')
                    ->label('抬头')
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tax_id')
                    ->label('税号')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('邮箱')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ]))
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoiceRequests::route('/'),
            'edit' => Pages\EditInvoiceRequest::route('/{record}/edit'),
        ];
    }
}
