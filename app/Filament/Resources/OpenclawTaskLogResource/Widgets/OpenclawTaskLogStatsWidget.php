<?php

namespace App\Filament\Resources\OpenclawTaskLogResource\Widgets;

use App\Models\OpenclawTaskLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

/**
 * 对齐 Vue 版「任务日志」页的近 N 日汇总（原接口：GET /api/admin/openclaw-task-logs/stats）。
 * 放在 Resource 子目录，避免被全局 discoverWidgets 挂到仪表盘。
 */
class OpenclawTaskLogStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        if (! Schema::hasTable('openclaw_task_logs')) {
            return [
                Stat::make('任务日志', '表未就绪')->description('请先执行 migrate'),
            ];
        }

        $days = 7;
        $from = now()->subDays($days - 1)->startOfDay();
        $q = OpenclawTaskLog::query()->where('started_at', '>=', $from);

        $total = (clone $q)->count();
        $success = (clone $q)->where('status', OpenclawTaskLog::STATUS_SUCCESS)->count();
        $error = (clone $q)->where('status', OpenclawTaskLog::STATUS_ERROR)->count();
        $timeout = (clone $q)->where('status', OpenclawTaskLog::STATUS_TIMEOUT)->count();
        $skipped = (clone $q)->where('status', OpenclawTaskLog::STATUS_SKIPPED)->count();
        $avgMs = (int) round((clone $q)->whereNotNull('duration_ms')->avg('duration_ms') ?? 0);

        return [
            Stat::make("近 {$days} 日执行次数", number_format($total))
                ->description('含成功/失败/超时/跳过')
                ->icon('heroicon-o-chart-bar-square'),
            Stat::make('成功', number_format($success))
                ->description('状态为 success')
                ->color('success')
                ->icon('heroicon-o-check-circle'),
            Stat::make('失败', number_format($error))
                ->description('状态为 error')
                ->color('danger')
                ->icon('heroicon-o-x-circle'),
            Stat::make('超时 / 跳过', number_format($timeout).' / '.number_format($skipped))
                ->description('timeout / skipped')
                ->color('warning')
                ->icon('heroicon-o-clock'),
            Stat::make('平均耗时', $avgMs > 0 ? $avgMs.' ms' : '—')
                ->description('有 duration_ms 的记录')
                ->icon('heroicon-o-bolt'),
        ];
    }
}
