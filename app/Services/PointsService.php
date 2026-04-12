<?php

namespace App\Services;

use App\Models\Point;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class PointsService
{
    /**
     * 增加积分并写入流水（正数）。
     */
    public static function earn(User $user, int $amount, string $category, string $description, ?string $referenceType = null, ?int $referenceId = null): void
    {
        if ($amount <= 0 || ! Schema::hasTable('points') || ! Schema::hasColumn('users', 'points_balance')) {
            return;
        }

        DB::transaction(function () use ($user, $amount, $category, $description, $referenceType, $referenceId) {
            /** @var User $locked */
            $locked = User::query()->whereKey($user->id)->lockForUpdate()->first();
            if (! $locked) {
                return;
            }
            $prev = (int) $locked->points_balance;
            $next = $prev + $amount;

            Point::query()->create([
                'user_id' => $locked->id,
                'amount' => $amount,
                'balance' => $next,
                'type' => 'earn',
                'category' => $category,
                'description' => $description,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'created_at' => now(),
            ]);

            $locked->forceFill(['points_balance' => $next])->save();
        });
    }

    /**
     * 扣减积分并写入流水（负数 amount 存库，balance 递减）。
     */
    public static function spend(User $user, int $amount, string $category, string $description, ?string $referenceType = null, ?int $referenceId = null): bool
    {
        if ($amount <= 0 || ! Schema::hasTable('points') || ! Schema::hasColumn('users', 'points_balance')) {
            return false;
        }

        $ok = false;
        DB::transaction(function () use ($user, $amount, $category, $description, $referenceType, $referenceId, &$ok) {
            /** @var User|null $locked */
            $locked = User::query()->whereKey($user->id)->lockForUpdate()->first();
            if (! $locked) {
                return;
            }
            $prev = (int) $locked->points_balance;
            if ($prev < $amount) {
                return;
            }
            $next = $prev - $amount;

            Point::query()->create([
                'user_id' => $locked->id,
                'amount' => -$amount,
                'balance' => $next,
                'type' => 'spend',
                'category' => $category,
                'description' => $description,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'created_at' => now(),
            ]);

            $locked->forceFill(['points_balance' => $next])->save();
            $ok = true;
        });

        return $ok;
    }

    /**
     * 每日首次登录赠送（幂等：同日同 category 不再发）。
     */
    public static function tryDailyLoginReward(User $user): void
    {
        $amt = (int) config('points_rewards.login_daily', 0);
        if ($amt <= 0 || ! Schema::hasTable('points')) {
            return;
        }

        $start = now()->startOfDay();
        $exists = Point::query()
            ->where('user_id', $user->id)
            ->where('category', 'login_daily')
            ->where('created_at', '>=', $start)
            ->exists();
        if ($exists) {
            return;
        }

        self::earn($user, $amt, 'login_daily', '每日登录礼包', null, null);
    }
}
