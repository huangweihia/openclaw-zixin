<?php

namespace App\Filament\Resources;

use App\Models\RefundRequest;
use App\Filament\Resources\RefundRequestResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RefundRequestResource extends BaseAdminResource
{
    protected static ?string $model = RefundRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static ?string $navigationGroup = '订单与财务';

    protected static ?int $navigationSort = 50;

    protected static ?string $modelLabel = '退款申请';

    protected static ?string $pluralModelLabel = '退款申请';

    /** @return array<string, string> */
    public static function reasonOptions(): array
    {
        return [
            'not_as_described' => '与描述不符',
            'technical_issue' => '技术问题',
            'changed_mind' => '改变主意',
            'other' => '其他',
        ];
    }

    /** @return array<string, string> */
    public static function statusOptions(): array
    {
        return [
            'pending' => '待处理',
            'approved' => '已通过',
            'rejected' => '已拒绝',
            'completed' => '已完成',
        ];
    }

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
        return parent::getEloquentQuery()->with(['user', 'order', 'processor']);
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
                ->disabled(fn (?RefundRequest $record): bool => $record !== null),
            Forms\Components\Select::make('order_id')
                ->label('订单')
                ->relationship('order', 'order_no')
                ->searchable()
                ->preload()
                ->required()
                ->disabled(fn (?RefundRequest $record): bool => $record !== null),
            Forms\Components\Select::make('reason')
                ->label('退款原因')
                ->options(static::reasonOptions())
                ->required()
                ->native(false),
            Forms\Components\Textarea::make('description')
                ->label('退款说明')
                ->columnSpanFull()
                ->rows(4),
            Forms\Components\Select::make('status')
                ->label('状态')
                ->options(static::statusOptions())
                ->required()
                ->native(false),
            Forms\Components\TextInput::make('refund_amount')
                ->label('退款金额')
                ->numeric()
                ->step(0.01)
                ->required(),
            Forms\Components\Textarea::make('admin_note')
                ->label('管理员备注')
                ->columnSpanFull()
                ->rows(4),
            Forms\Components\DateTimePicker::make('processed_at')
                ->label('处理时间'),
            Forms\Components\Select::make('processed_by')
                ->label('处理人')
                ->relationship('processor', 'name')
                ->searchable()
                ->preload()
                ->nullable(),
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
                Tables\Columns\TextColumn::make('reason')
                    ->label('原因')
                    ->formatStateUsing(fn (?string $state): string => static::reasonOptions()[$state ?? ''] ?? ($state ?? '—'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('refund_amount')
                    ->label('退款金额')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('状态')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => static::statusOptions()[$state ?? ''] ?? (string) $state)
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved', 'completed' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('processor.name')
                    ->label('处理人')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListRefundRequests::route('/'),
            'edit' => Pages\EditRefundRequest::route('/{record}/edit'),
        ];
    }
}
