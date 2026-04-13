<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PointPackageResource\Pages;
use App\Models\PointPackage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PointPackageResource extends BaseAdminResource
{
    protected static ?string $model = PointPackage::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationGroup = '用户与会员';

    protected static ?int $navigationSort = 15;

    protected static ?string $modelLabel = '积分套餐';

    protected static ?string $pluralModelLabel = '积分套餐';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(120),
            Forms\Components\TextInput::make('points_amount')->numeric()->required()->minValue(1),
            Forms\Components\TextInput::make('bonus_points')->numeric()->default(0)->minValue(0)->label('赠送积分'),
            Forms\Components\TextInput::make('price_yuan')->numeric()->required()->minValue(0.01)->step(0.01),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\DateTimePicker::make('active_from')->label('开始时间')->seconds(false)->nullable(),
            Forms\Components\DateTimePicker::make('active_until')->label('结束时间')->seconds(false)->nullable(),
            Forms\Components\TextInput::make('badge')->maxLength(40)->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('points_amount')->label('积分')->sortable(),
                Tables\Columns\TextColumn::make('bonus_points')->label('赠送')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('total_points')
                    ->label('到账总积分')
                    ->getStateUsing(fn (PointPackage $record): int => $record->totalPointsNow())
                    ->sortable(false),
                Tables\Columns\TextColumn::make('price_yuan')->label('价格(元)')->sortable(),
                Tables\Columns\TextColumn::make('active_window')
                    ->label('时间限制')
                    ->getStateUsing(function (PointPackage $record): string {
                        $from = $record->active_from?->format('m-d H:i');
                        $until = $record->active_until?->format('m-d H:i');
                        if (! $from && ! $until) {
                            return '长期有效';
                        }

                        return ($from ?: '即刻').' ~ '.($until ?: '长期');
                    })
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ]))
            ->defaultSort('sort_order')
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
            'index' => Pages\ListPointPackages::route('/'),
            'create' => Pages\CreatePointPackage::route('/create'),
            'edit' => Pages\EditPointPackage::route('/{record}/edit'),
        ];
    }
}
