<?php

namespace App\Filament\Resources;

use App\Models\SvipCustomSubscription;
use App\Support\FilamentJson;
use Illuminate\Database\Eloquent\Builder;
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->required(),
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
            Forms\Components\DateTimePicker::make('expires_at'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('用户')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('plan_name')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()->toggleable(),
                Tables\Columns\TextColumn::make('amount')->money('CNY')->toggleable(),
                Tables\Columns\TextColumn::make('started_at')->dateTime()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('expires_at')->dateTime()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
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
            Infolists\Components\Section::make('方案与用户')
                ->icon('heroicon-o-user-circle')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('id')->label('编号'),
                    Infolists\Components\TextEntry::make('user.name')
                        ->label('用户')
                        ->placeholder('—'),
                    Infolists\Components\TextEntry::make('plan_name')->label('方案名称')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('status')
                        ->label('状态')
                        ->badge(),
                    Infolists\Components\TextEntry::make('amount')
                        ->label('金额')
                        ->money('CNY'),
                    Infolists\Components\TextEntry::make('duration_days')->label('时长(天)'),
                ]),
            Infolists\Components\Section::make('交付偏好')
                ->icon('heroicon-o-truck')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('delivery_frequency')->label('交付频率'),
                    Infolists\Components\TextEntry::make('preferred_send_time')->label('期望时间'),
                    Infolists\Components\TextEntry::make('delivery_channel')->label('交付渠道')->columnSpanFull(),
                ])
                ->collapsible(),
            Infolists\Components\Section::make('说明与服务项')
                ->icon('heroicon-o-clipboard-document-list')
                ->schema([
                    Infolists\Components\TextEntry::make('description')
                        ->label('方案说明')
                        ->placeholder('—')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('services')
                        ->label('服务清单 (JSON)')
                        ->formatStateUsing(fn ($state): string => FilamentJson::pretty($state))
                        ->columnSpanFull()
                        ->extraAttributes(['class' => 'font-mono text-xs whitespace-pre-wrap max-h-64 overflow-y-auto']),
                ])
                ->collapsible(),
            Infolists\Components\Section::make('有效期')
                ->icon('heroicon-o-calendar-days')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('started_at')->label('开始时间')->dateTime(),
                    Infolists\Components\TextEntry::make('expires_at')->label('到期时间')->dateTime(),
                    Infolists\Components\TextEntry::make('created_at')->label('创建时间')->dateTime(),
                    Infolists\Components\TextEntry::make('updated_at')->label('更新时间')->dateTime(),
                ])
                ->collapsed(),
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
