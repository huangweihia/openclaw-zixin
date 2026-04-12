<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSubscriptionTopicTemplate extends Model
{
    protected $fillable = [
        'topic_key',
        'template_key',
    ];

    /** @var array<string, string> */
    public const TOPIC_LABELS = [
        'subscription_saved' => '订阅保存确认（保存后立即发信）',
        'daily' => '每日精选（定时任务）',
        'weekly' => '每周精选（定时任务）',
        'notification' => '系统通知（批次）',
        'promotion' => '活动推广（批次）',
    ];
}
