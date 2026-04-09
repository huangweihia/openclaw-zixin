<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * OpenClaw 定时任务日志模型
 * 
 * 记录所有 OpenClaw 定时任务的执行情况
 * - 不包含具体内容
 * - 只记录数据量、状态、错误信息
 */
class OpenclawTaskLog extends Model
{
    use HasFactory;

    protected $table = 'openclaw_task_logs';

    protected $fillable = [
        'task_name',
        'task_id',
        'task_type',
        'status',
        'duration_ms',
        'data_summary',
        'total_items',
        'success_count',
        'failed_count',
        'skipped_count',
        'api_endpoint',
        'push_status',
        'push_response',
        'error_message',
        'error_details',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'data_summary' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'duration_ms' => 'integer',
        'total_items' => 'integer',
        'success_count' => 'integer',
        'failed_count' => 'integer',
        'skipped_count' => 'integer',
    ];

    // 任务类型常量
    const TYPE_AI_CONTENT = 'ai_content';
    const TYPE_SVIP_SUBSCRIPTION = 'svip_subscription';
    const TYPE_SVIP_CONTENT = 'svip_content';
    const TYPE_DAILY_NEWS = 'daily_news';

    // 状态常量
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';
    const STATUS_TIMEOUT = 'timeout';
    const STATUS_SKIPPED = 'skipped';

    // 推送状态常量
    const PUSH_SUCCESS = 'success';
    const PUSH_FAILED = 'failed';
    const PUSH_NOT_ATTEMPTED = 'not_attempted';

    /**
     * 创建成功日志
     */
    public static function logSuccess(
        string $taskName,
        string $taskType,
        int $durationMs,
        array $dataSummary,
        int $totalItems,
        string $apiEndpoint = null,
        string $pushResponse = null
    ): self {
        return self::create([
            'task_name' => $taskName,
            'task_type' => $taskType,
            'status' => self::STATUS_SUCCESS,
            'duration_ms' => $durationMs,
            'data_summary' => $dataSummary,
            'total_items' => $totalItems,
            'success_count' => $totalItems,
            'api_endpoint' => $apiEndpoint,
            'push_status' => $pushResponse ? self::PUSH_SUCCESS : self::PUSH_NOT_ATTEMPTED,
            'push_response' => $pushResponse,
            'started_at' => now()->subMilliseconds($durationMs),
            'finished_at' => now(),
        ]);
    }

    /**
     * 创建错误日志
     */
    public static function logError(
        string $taskName,
        string $taskType,
        string $errorMessage,
        string $errorDetails = null,
        int $durationMs = null
    ): self {
        return self::create([
            'task_name' => $taskName,
            'task_type' => $taskType,
            'status' => self::STATUS_ERROR,
            'duration_ms' => $durationMs,
            'error_message' => $errorMessage,
            'error_details' => $errorDetails,
            'started_at' => $durationMs ? now()->subMilliseconds($durationMs) : now(),
            'finished_at' => now(),
        ]);
    }

    /**
     * 创建超时日志
     */
    public static function logTimeout(
        string $taskName,
        string $taskType,
        int $timeoutMs
    ): self {
        return self::create([
            'task_name' => $taskName,
            'task_type' => $taskType,
            'status' => self::STATUS_TIMEOUT,
            'duration_ms' => $timeoutMs,
            'error_message' => '任务执行超时',
            'started_at' => now()->subMilliseconds($timeoutMs),
            'finished_at' => now(),
        ]);
    }

    /**
     * 范围查询：按任务类型
     */
    public function scopeOfType($query, string $taskType)
    {
        return $query->where('task_type', $taskType);
    }

    /**
     * 范围查询：按状态
     */
    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * 范围查询：今天的数据
     */
    public function scopeToday($query)
    {
        return $query->whereDate('started_at', today());
    }

    /**
     * 范围查询：最近 N 天
     */
    public function scopeRecentDays($query, int $days)
    {
        return $query->where('started_at', '>=', now()->subDays($days));
    }

    /**
     * 获取任务统计信息
     */
    public static function getTodayStats(string $taskType = null): array
    {
        $query = self::today();
        
        if ($taskType) {
            $query->ofType($taskType);
        }

        $stats = $query->selectRaw('
            status,
            COUNT(*) as count,
            SUM(total_items) as total_items,
            AVG(duration_ms) as avg_duration
        ')->groupBy('status')->get();

        return $stats->toArray();
    }
}
