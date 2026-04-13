<?php

namespace App\Filament\Resources;

use App\Models\EmailLog;
use App\Filament\Resources\EmailLogResource\Pages;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class EmailLogResource extends BaseAdminResource
{
    protected static ?string $model = EmailLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = '营销与触达';

    protected static ?int $navigationSort = 50;

    protected static ?string $modelLabel = '邮件记录';

    protected static ?string $pluralModelLabel = '邮件记录';

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
                ->nullable(),
            Forms\Components\TextInput::make('template_key')->maxLength(65535),
            Forms\Components\TextInput::make('to')->maxLength(65535),
            Forms\Components\TextInput::make('subject')->maxLength(65535),
            Forms\Components\TextInput::make('status')->maxLength(65535),
            Forms\Components\TextInput::make('error_message')->maxLength(65535),
            Forms\Components\DateTimePicker::make('sent_at'),
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
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('template_key')
                    ->label('模板键')
                    ->limit(40)
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('meta')
                    ->label('元数据')
                    ->formatStateUsing(function ($state): string {
                        if (! is_array($state) || $state === []) {
                            return '—';
                        }

                        return (string) json_encode($state, JSON_UNESCAPED_UNICODE);
                    })
                    ->limit(80)
                    ->tooltip(function (EmailLog $record): ?string {
                        $m = $record->meta;
                        if (! is_array($m) || $m === []) {
                            return null;
                        }

                        return json_encode($m, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('to')->limit(36)->searchable(),
                Tables\Columns\TextColumn::make('subject')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'sent', 'delivered' => 'success',
                        'failed', 'error' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sent_at')->dateTime()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ]))
            ->defaultSort('sent_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('状态')
                    ->options([
                        'sent' => '已发送',
                        'failed' => '失败',
                        'pending' => '待发送',
                    ]),
                Tables\Filters\SelectFilter::make('template_key')
                    ->label('模板键')
                    ->options([
                        'daily_digest' => '每日摘要',
                        'weekly_digest' => '每周摘要',
                        'subscription_saved' => '订阅确认',
                        'subscription_saved_fallback' => '订阅确认（兜底）',
                        'register_welcome' => '注册欢迎',
                        'register_verification' => '注册验证码',
                        'profile_email_change_code' => '修改邮箱验证码',
                        'mini_bind_email' => '小程序绑定邮箱',
                        'membership_expiry_reminder' => '会员到期提醒',
                        'admin_mail_test' => '后台发信测试',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('发送概况')
                ->icon('heroicon-o-envelope')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('id')->label('编号'),
                    Infolists\Components\TextEntry::make('status')
                        ->label('状态')
                        ->badge()
                        ->color(fn (?string $state): string => match ($state) {
                            'sent', 'delivered' => 'success',
                            'failed', 'error' => 'danger',
                            default => 'gray',
                        }),
                    Infolists\Components\TextEntry::make('user.name')
                        ->label('关联用户')
                        ->placeholder('—'),
                    Infolists\Components\TextEntry::make('template_key')->label('模板键')->copyable(),
                    Infolists\Components\TextEntry::make('to')->label('收件人')->copyable()->columnSpanFull(),
                    Infolists\Components\TextEntry::make('subject')->label('主题')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('sent_at')->label('发送时间')->dateTime(),
                ]),
            Infolists\Components\Section::make('错误与元数据')
                ->icon('heroicon-o-exclamation-triangle')
                ->collapsed()
                ->schema([
                    Infolists\Components\TextEntry::make('meta')
                        ->label('扩展元数据（订阅 ID、计划时刻等）')
                        ->formatStateUsing(function ($state): string {
                            if (! is_array($state) || $state === []) {
                                return '—';
                            }

                            return (string) json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                        })
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('error_message')
                        ->label('错误信息')
                        ->placeholder('无')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('created_at')->label('创建时间')->dateTime(),
                    Infolists\Components\TextEntry::make('updated_at')->label('更新时间')->dateTime(),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\EmailLogResource\Pages\ListEmailLogs::route('/'),
            'view' => \App\Filament\Resources\EmailLogResource\Pages\ViewEmailLog::route('/{record}'),
        ];
    }
}
