<?php

namespace App\Filament\Resources;

use App\Models\Point;
use App\Filament\Resources\PointResource\Pages;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class PointResource extends BaseAdminResource
{
    protected static ?string $model = Point::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = '用户与会员';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = '积分流水';

    protected static ?string $pluralModelLabel = '积分流水';

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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('user_id')->numeric(),
            Forms\Components\TextInput::make('amount')->numeric()->step(0.01),
            Forms\Components\TextInput::make('balance')->maxLength(65535),
            Forms\Components\TextInput::make('type')->maxLength(65535),
            Forms\Components\TextInput::make('category')->maxLength(65535),
            Forms\Components\Textarea::make('description')->columnSpanFull()->rows(6),
            Forms\Components\TextInput::make('reference_type')->maxLength(65535),
            Forms\Components\TextInput::make('reference_id')->numeric(),
            Forms\Components\DateTimePicker::make('created_at'),
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
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('变动')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($state): string => (float) $state >= 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('balance')->label('余额')->toggleable(),
                Tables\Columns\TextColumn::make('type')->badge()->toggleable(),
                Tables\Columns\TextColumn::make('category')->limit(24)->toggleable(),
                Tables\Columns\TextColumn::make('description')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('流水概要')
                ->icon('heroicon-o-banknotes')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('id')->label('编号'),
                    Infolists\Components\TextEntry::make('user.name')
                        ->label('用户')
                        ->placeholder('—'),
                    Infolists\Components\TextEntry::make('amount')
                        ->label('变动值')
                        ->badge()
                        ->color(fn ($state): string => (float) $state >= 0 ? 'success' : 'danger'),
                    Infolists\Components\TextEntry::make('balance')->label('变动后余额'),
                    Infolists\Components\TextEntry::make('type')->label('类型')->badge(),
                    Infolists\Components\TextEntry::make('category')->label('分类'),
                    Infolists\Components\TextEntry::make('created_at')->label('发生时间')->dateTime(),
                ]),
            Infolists\Components\Section::make('说明与关联')
                ->icon('heroicon-o-link')
                ->schema([
                    Infolists\Components\TextEntry::make('description')
                        ->label('说明')
                        ->placeholder('—')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('reference_type')
                        ->label('关联类型')
                        ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—'),
                    Infolists\Components\TextEntry::make('reference_id')->label('关联 ID'),
                ])
                ->columns(2)
                ->collapsible(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\PointResource\Pages\ListPoints::route('/'),
            'view' => \App\Filament\Resources\PointResource\Pages\ViewPoint::route('/{record}'),
        ];
    }
}
