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
        return parent::getEloquentQuery()->with(['user', 'reference']);
    }

    /** @return array<string, string> */
    public static function typeLabels(): array
    {
        return [
            'earn' => '获得',
            'spend' => '消费',
        ];
    }

    /** @return array<string, string> */
    public static function categoryLabels(): array
    {
        return [
            'register' => '注册奖励',
            'login' => '登录',
            'order' => '订单',
            'admin' => '管理员调整',
            'consume' => '消费',
            'refund' => '退款',
            'boost' => '加热消耗',
            'post_approve' => '投稿审核通过',
            'login_daily' => '每日登录',
            'point_purchase' => '积分套餐购买',
            'post_liked' => '投稿被赞',
            'post_favorited' => '投稿被收藏',
            'post_commented' => '投稿被评论',
        ];
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
            ->columns(static::searchableColumns([
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
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => static::typeLabels()[$state ?? ''] ?? (string) $state)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('category')
                    ->limit(24)
                    ->formatStateUsing(fn (?string $state): string => static::categoryLabels()[$state ?? ''] ?? (string) $state)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('description')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(),
            ]))
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
                    Infolists\Components\TextEntry::make('type')
                        ->label('类型')
                        ->badge()
                        ->formatStateUsing(fn (?string $state): string => static::typeLabels()[$state ?? ''] ?? (string) $state),
                    Infolists\Components\TextEntry::make('category')
                        ->label('分类')
                        ->formatStateUsing(fn (?string $state): string => static::categoryLabels()[$state ?? ''] ?? (string) $state),
                    Infolists\Components\TextEntry::make('created_at')->label('发生时间')->dateTime(),
                ]),
            Infolists\Components\Section::make('说明与关联')
                ->icon('heroicon-o-link')
                ->schema([
                    Infolists\Components\TextEntry::make('description')
                        ->label('说明')
                        ->placeholder('—')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('reference_label')
                        ->label('关联对象')
                        ->getStateUsing(function (Point $record): string {
                            if (! $record->reference_type || ! $record->reference_id) {
                                return '—';
                            }
                            $record->loadMissing('reference');
                            $ref = $record->reference;
                            $typeZh = match (class_basename((string) $record->reference_type)) {
                                'Article' => '文章',
                                'Order' => '订单',
                                'UserPost' => '动态',
                                default => class_basename((string) $record->reference_type),
                            };
                            if ($ref === null) {
                                return $typeZh.' #'.(string) $record->reference_id;
                            }
                            $title = $ref->title ?? $ref->name ?? $ref->order_no ?? null;

                            return $title !== null && $title !== ''
                                ? $typeZh.'：'.$title
                                : $typeZh.' #'.(string) $record->reference_id;
                        })
                        ->columnSpanFull(),
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
