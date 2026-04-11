<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * 个人中心「全站动态」等共用的订阅开通流水（脱敏展示）。
 */
final class VipActivityFeed
{
    /**
     * 为演示环境补足订阅流水（仅在 subscriptions 表存在且数据不足时插入）。
     */
    public static function seedDemoIfNeeded(int $target = 50): void
    {
        if (! Schema::hasTable('subscriptions') || ! Schema::hasTable('users')) {
            return;
        }

        $existing = (int) DB::table('subscriptions')->count();
        if ($existing >= $target) {
            return;
        }

        $users = DB::table('users')->orderByDesc('id')->limit(30)->get(['id', 'name']);
        if ($users->isEmpty()) {
            return;
        }

        $need = $target - $existing;
        $now = now();
        $rows = [];
        $plans = [
            ['plan' => 'monthly', 'amount' => 29.00, 'days' => 30],
            ['plan' => 'yearly', 'amount' => 199.00, 'days' => 365],
            ['plan' => 'lifetime', 'amount' => 999.00, 'days' => 3650],
        ];

        for ($i = 0; $i < $need; $i++) {
            $u = $users[$i % $users->count()];
            $pick = $plans[$i % count($plans)];
            $createdAt = $now->copy()->subMinutes($i * 35 + random_int(0, 20));
            $startedAt = $createdAt->copy();
            $expiresAt = $startedAt->copy()->addDays($pick['days']);

            $rows[] = [
                'user_id' => $u->id,
                'plan' => $pick['plan'],
                'amount' => $pick['amount'],
                'status' => 'active',
                'started_at' => $startedAt,
                'expires_at' => $expiresAt,
                'payment_id' => null,
                'payment_method' => 'demo',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }

        DB::table('subscriptions')->insert($rows);
    }

    public static function recent(int $limit = 50): Collection
    {
        if (! Schema::hasTable('subscriptions')) {
            return collect();
        }

        return DB::table('subscriptions')
            ->join('users', 'subscriptions.user_id', '=', 'users.id')
            ->where('subscriptions.status', 'active')
            ->whereIn('subscriptions.plan', ['monthly', 'yearly', 'lifetime'])
            ->orderByDesc('subscriptions.created_at')
            ->limit($limit)
            ->get([
                'users.name',
                'subscriptions.plan',
                'subscriptions.amount',
                'subscriptions.created_at',
            ])
            ->map(function ($item) {
                $item->name = Str::limit($item->name, 1, '***');
                $item->plan_text = self::planText($item->plan);
                $item->created_at = Carbon::parse($item->created_at);

                return $item;
            });
    }

    public static function planText(?string $plan): string
    {
        return match ($plan) {
            'monthly' => '月度会员',
            'yearly' => '年度会员',
            'lifetime' => '终身会员',
            default => (string) $plan,
        };
    }
}
