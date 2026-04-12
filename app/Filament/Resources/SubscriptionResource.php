<?php

namespace App\Filament\Resources;

use App\Models\User;
use App\Filament\Resources\SubscriptionResource\Pages;
use App\Filament\Resources\SubscriptionResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionResource extends BaseAdminResource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'subscriptions';

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = '订单与财务';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = '订阅用户';

    protected static ?string $pluralModelLabel = '会员订阅';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('subscriptions')
            ->withCount('subscriptions')
            ->withMax('subscriptions', 'created_at');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('用户信息')
                ->schema([
                    Infolists\Components\TextEntry::make('id')->label('用户 ID'),
                    Infolists\Components\TextEntry::make('name')->label('昵称'),
                    Infolists\Components\TextEntry::make('email')->label('邮箱'),
                    Infolists\Components\TextEntry::make('role')
                        ->label('角色')
                        ->badge()
                        ->formatStateUsing(fn (?string $state): string => match ($state) {
                            'user' => '普通用户',
                            'vip' => 'VIP',
                            'svip' => 'SVIP',
                            'admin' => '管理员',
                            default => (string) $state,
                        }),
                    Infolists\Components\TextEntry::make('subscription_ends_at')
                        ->label('会员到期')
                        ->dateTime()
                        ->placeholder('—'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('用户')
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->limit(48)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('角色')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'user' => '普通用户',
                        'vip' => 'VIP',
                        'svip' => 'SVIP',
                        'admin' => '管理员',
                        default => (string) $state,
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('subscriptions_count')
                    ->label('订阅笔数')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subscriptions_max_created_at')
                    ->label('最近一笔')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
            ]))
            ->defaultSort('subscriptions_max_created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make()->label('订阅详情'),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SubscriptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptionUsers::route('/'),
            'view' => Pages\ViewSubscriptionUser::route('/{record}'),
        ];
    }
}
