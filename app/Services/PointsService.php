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
}
