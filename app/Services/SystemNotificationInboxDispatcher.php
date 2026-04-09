<?php

namespace App\Services;

use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SystemNotificationInboxDispatcher
{
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

        User::query()
            ->where('is_banned', false)
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
