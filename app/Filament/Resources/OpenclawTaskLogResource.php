<?php

namespace App\Filament\Resources;

use App\Models\OpenclawTaskLog;
use App\Filament\Resources\OpenclawTaskLogResource\Pages;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Resources\BaseAdminResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class OpenclawTaskLogResource extends BaseAdminResource
{
    protected static ?string $model = OpenclawTaskLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-command-line';

    protected static ?string $navigationGroup = '运营与自动化';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'OpenClaw 日志';

    protected static ?string $pluralModelLabel = 'OpenClaw 日志';

    /** @return array<string, string> */
    public static function taskTypeLabels(): array
    {
        return [
            OpenclawTaskLog::TYPE_AI_CONTENT => 'AI 内容采集',
            OpenclawTaskLog::TYPE_SVIP_SUBSCRIPTION => 'SVIP 订阅',
            OpenclawTaskLog::TYPE_SVIP_CONTENT => 'SVIP 内容',
            OpenclawTaskLog::TYPE_DAILY_NEWS => '日报',
        ];
    }

    /** @return array<string, string> */
    public static function statusLabels(): array
    {
        return [
            OpenclawTaskLog::STATUS_SUCCESS => '成功',
            OpenclawTaskLog::STATUS_ERROR => '失败',
            OpenclawTaskLog::STATUS_TIMEOUT => '超时',
            OpenclawTaskLog::STATUS_SKIPPED => '跳过',
        ];
    }

    /** @return array<string, string> */
    public static function pushStatusLabels(): array
    {
        return [
            OpenclawTaskLog::PUSH_SUCCESS => '推送成功',
            OpenclawTaskLog::PUSH_FAILED => '推送失败',
            OpenclawTaskLog::PUSH_NOT_ATTEMPTED => '未尝试推送',
        ];
    }

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
        return static::canViewAny() && true;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('task_name')->maxLength(65535),
            Forms\Components\TextInput::make('task_id')->numeric(),
            Forms\Components\TextInput::make('task_type')->maxLength(65535),
            Forms\Components\TextInput::make('status')->maxLength(65535),
            Forms\Components\TextInput::make('duration_ms')->numeric(),
            Forms\Components\Textarea::make('data_summary')->columnSpanFull()->helperText('JSON 数组，可手填'),
            Forms\Components\TextInput::make('total_items')->numeric(),
            Forms\Components\TextInput::make('success_count')->numeric(),
            Forms\Components\TextInput::make('failed_count')->numeric(),
            Forms\Components\TextInput::make('skipped_count')->numeric(),
            Forms\Components\TextInput::make('api_endpoint')->maxLength(65535),
            Forms\Components\TextInput::make('push_status')->maxLength(65535),
            Forms\Components\TextInput::make('push_response')->maxLength(65535),
            Forms\Components\TextInput::make('error_message')->maxLength(65535),
            Forms\Components\TextInput::make('error_details')->maxLength(65535),
            Forms\Components\DateTimePicker::make('started_at'),
            Forms\Components\DateTimePicker::make('finished_at'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::searchableColumns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('task_name')
                    ->limit(36)
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('task_id')->limit(8)->toggleable(),
                Tables\Columns\TextColumn::make('task_type')
                    ->formatStateUsing(fn (?string $state): string => static::taskTypeLabels()[$state ?? ''] ?? ($state ?? '—'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => static::statusLabels()[$state ?? ''] ?? ($state ?? '—'))
                    ->color(fn (?string $state): string => match ($state) {
                        OpenclawTaskLog::STATUS_SUCCESS => 'success',
                        OpenclawTaskLog::STATUS_ERROR => 'danger',
                        OpenclawTaskLog::STATUS_TIMEOUT => 'warning',
                        OpenclawTaskLog::STATUS_SKIPPED => 'gray',
                        default => 'gray',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('push_status')
                    ->label('推送')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => static::pushStatusLabels()[$state ?? ''] ?? ($state ?? '—'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('duration_ms')->toggleable(),
                Tables\Columns\TextColumn::make('data_summary')->limit(48)->toggleable(),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('开始时间')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('started_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('task_type')
                    ->label('任务类型')
                    ->options(static::taskTypeLabels()),
                Tables\Filters\SelectFilter::make('status')
                    ->label('执行状态')
                    ->options(static::statusLabels()),
                Tables\Filters\SelectFilter::make('push_status')
                    ->label('推送状态')
                    ->options(static::pushStatusLabels()),
                Tables\Filters\Filter::make('started_between')
                    ->label('开始时间范围')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('从'),
                        Forms\Components\DatePicker::make('to')->label('到'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $d) => $q->whereDate('started_at', '>=', $d))
                            ->when($data['to'] ?? null, fn (Builder $q, $d) => $q->whereDate('started_at', '<=', $d));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('任务概况')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('id')->label('编号'),
                    Infolists\Components\TextEntry::make('task_name')->label('任务名称')->copyable(),
                    Infolists\Components\TextEntry::make('task_id')->label('关联任务 ID')->placeholder('—'),
                    Infolists\Components\TextEntry::make('task_type')
                        ->label('任务类型')
                        ->formatStateUsing(fn (?string $state): string => static::taskTypeLabels()[$state ?? ''] ?? ($state ?? '—')),
                    Infolists\Components\TextEntry::make('status')
                        ->label('执行状态')
                        ->badge()
                        ->formatStateUsing(fn (?string $state): string => static::statusLabels()[$state ?? ''] ?? ($state ?? '—'))
                        ->color(fn (?string $state): string => match ($state) {
                            OpenclawTaskLog::STATUS_SUCCESS => 'success',
                            OpenclawTaskLog::STATUS_ERROR => 'danger',
                            OpenclawTaskLog::STATUS_TIMEOUT => 'warning',
                            OpenclawTaskLog::STATUS_SKIPPED => 'gray',
                            default => 'gray',
                        }),
                    Infolists\Components\TextEntry::make('duration_ms')->label('耗时（毫秒）'),
                    Infolists\Components\TextEntry::make('started_at')->label('开始时间')->dateTime(),
                    Infolists\Components\TextEntry::make('finished_at')->label('结束时间')->dateTime(),
                ]),
            Infolists\Components\Section::make('数据统计')
                ->columns(4)
                ->schema([
                    Infolists\Components\TextEntry::make('total_items')->label('总条数')->placeholder('—'),
                    Infolists\Components\TextEntry::make('success_count')->label('成功数')->placeholder('—'),
                    Infolists\Components\TextEntry::make('failed_count')->label('失败数')->placeholder('—'),
                    Infolists\Components\TextEntry::make('skipped_count')->label('跳过数')->placeholder('—'),
                ]),
            Infolists\Components\Section::make('数据摘要')
                ->schema([
                    Infolists\Components\TextEntry::make('data_summary')
                        ->label('摘要内容')
                        ->formatStateUsing(function ($state): string {
                            if ($state === null || $state === '') {
                                return '—';
                            }
                            if (is_array($state)) {
                                return json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                            }

                            return (string) $state;
                        })
                        ->columnSpanFull()
                        ->prose()
                        ->extraAttributes(['class' => 'font-mono text-sm whitespace-pre-wrap']),
                ])
                ->collapsible(),
            Infolists\Components\Section::make('推送与接口')
                ->columns(1)
                ->schema([
                    Infolists\Components\TextEntry::make('api_endpoint')->label('推送接口')->placeholder('—')->copyable(),
                    Infolists\Components\TextEntry::make('push_status')
                        ->label('推送状态')
                        ->formatStateUsing(fn (?string $state): string => static::pushStatusLabels()[$state ?? ''] ?? ($state ?? '—'))
                        ->badge(),
                    Infolists\Components\TextEntry::make('push_response')->label('推送响应')->placeholder('—')->columnSpanFull(),
                ])
                ->collapsible(),
            Infolists\Components\Section::make('错误信息')
                ->schema([
                    Infolists\Components\TextEntry::make('error_message')->label('错误摘要')->placeholder('无')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('error_details')
                        ->label('错误详情')
                        ->placeholder('无')
                        ->formatStateUsing(function ($state): string {
                            if ($state === null || $state === '') {
                                return '—';
                            }
                            if (is_array($state)) {
                                return json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                            }
                            $s = (string) $state;
                            $decoded = json_decode($s, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                            }

                            return $s;
                        })
                        ->columnSpanFull()
                        ->extraAttributes(['class' => 'font-mono text-sm whitespace-pre-wrap max-h-96 overflow-y-auto']),
                ])
                ->collapsible(),
            Infolists\Components\Section::make('记录元数据')
                ->columns(2)
                ->collapsed()
                ->schema([
                    Infolists\Components\TextEntry::make('created_at')->label('创建时间')->dateTime(),
                    Infolists\Components\TextEntry::make('updated_at')->label('更新时间')->dateTime(),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\OpenclawTaskLogResource\Pages\ListOpenclawTaskLogs::route('/'),
            'view' => \App\Filament\Resources\OpenclawTaskLogResource\Pages\ViewOpenclawTaskLog::route('/{record}'),
        ];
    }
}
