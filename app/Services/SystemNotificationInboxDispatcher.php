<?php

namespace App\Services;

use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SystemNotificationInboxDispatcher
{
    public function dispatchBacklog(int $limit = 20): int
    {
        $count = 0;
        SystemNotification::query()
            ->where('is_published', true)
            ->whereNull('inbox_dispatched_at')
            ->orderBy('id')
            ->limit(max(1, $limit))
            ->get()
            ->each(function (SystemNotification $row) use (&$count) {
                $this->dispatchIfNeeded($row);
                $count++;
            });

        return $count;
    }

    /**
     * 将已发布的系统通知首次批量写入用户站内信（notifications 表）。
     */
    public function dispatchIfNeeded(SystemNotification $row): void
    {
        if (! $row->is_published || $row->inbox_dispatched_at !== null) {
            return;
        }

        $now = now();
        $title = $row->title;
        $content = $row->content;
        $actionUrl = $row->action_url;
        $audience = strtolower(trim((string) ($row->audience ?? 'all')));
        if ($audience === '') {
            $audience = 'all';
        }

        User::query()
            ->where('is_banned', false)
            ->when($audience !== 'all', function ($q) use ($audience) {
                return match ($audience) {
                    'admin' => $q->where('role', 'admin'),
                    'vip' => $q->whereIn('role', ['vip', 'admin']),
                    'svip' => $q->whereIn('role', ['svip', 'admin']),
                    'member' => $q->whereIn('role', ['vip', 'svip', 'admin']),
                    'non_member' => $q->whereNotIn('role', ['vip', 'svip', 'admin']),
                    // 'user'：非游客（这里是站内信写入，天然都是用户表，所以等价 all）
                    default => $q,
                };
            })
            ->orderBy('id')
            ->chunkById(500, function ($users) use ($title, $content, $actionUrl, $now) {
                $batch = [];
                foreach ($users as $u) {
                    $batch[] = [
                        'user_id' => $u->id,
                        'type' => 'system_announcement',
                        'title' => $title,
                        'content' => $content,
                        'action_url' => $actionUrl,
                        'is_read' => false,
                        'read_at' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                if ($batch !== []) {
                    DB::table('notifications')->insert($batch);
                }
            });

        $row->forceFill(['inbox_dispatched_at' => $now])->save();
    }
}
