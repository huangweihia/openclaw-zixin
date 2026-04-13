<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentBoostResource\Pages;
use App\Models\ContentBoost;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class ContentBoostResource extends BaseAdminResource
{
    protected static ?string $model = ContentBoost::class;

    protected static ?string $navigationIcon = 'heroicon-o-fire';

    protected static ?string $navigationGroup = '交易 · 用户 · 售后';

    protected static ?int $navigationSort = 52;

    protected static ?string $modelLabel = '加热记录';

    protected static ?string $pluralModelLabel = '加热记录';

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('actor.name')->label('加热用户')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('userPost.title')->label('投稿')->limit(38)->toggleable(),
                Tables\Columns\TextColumn::make('points_spent')->label('消耗积分')->sortable(),
                Tables\Columns\TextColumn::make('weight')->label('权重')->sortable(),
                Tables\Columns\TextColumn::make('starts_at')->label('开始')->dateTime()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('ends_at')->label('结束')->dateTime()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->label('创建时间')->dateTime()->sortable(),
            ]))
            ->defaultSort('id', 'desc')
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContentBoosts::route('/'),
        ];
    }
}
