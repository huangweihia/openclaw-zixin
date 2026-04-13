<?php

namespace App\Services;

use App\Models\ContentBoost;
use App\Models\InboxNotification;
use App\Models\User;
use App\Models\UserPost;
use App\Support\PointsRuleConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class UserPostBoostService
{
    public static function pointsCost(): int
    {
        return PointsRuleConfig::boostCost();
    }

    public static function weightForSpend(int $pointsSpent): int
    {
        $ratio = (float) config('boost.weight_per_point_ratio', 0.1);

        return max(1, (int) round($pointsSpent * $ratio));
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public static function boost(User $actor, UserPost $post): array
    {
        abort_unless($post->status === 'approved', 404);
        abort_unless(in_array($post->visibility, ['public', 'vip'], true), 404);

        if (! Schema::hasTable('content_boosts')) {
            return ['ok' => false, 'message' => '加热功能未启用'];
        }

        $cost = self::pointsCost();
        $cap = PointsRuleConfig::boostDailyCapPerPost();
        $todayStart = now()->startOfDay();
        $todayCount = ContentBoost::query()
            ->where('actor_user_id', $actor->id)
            ->where('user_post_id', $post->id)
            ->where('created_at', '>=', $todayStart)
            ->count();
        if ($todayCount >= $cap) {
            return ['ok' => false, 'message' => '今日对该投稿加热次数已达上限'];
        }

        $weight = self::weightForSpend($cost);
        $hours = PointsRuleConfig::boostWindowHours();
        $starts = now();
        $ends = now()->addHours($hours);

        $ok = false;
        DB::transaction(function () use ($actor, $post, $cost, $weight, $starts, $ends, &$ok) {
            if (! PointsService::spend(
                $actor,
                $cost,
                'boost',
                '投稿加热：「'.$post->title.'」',
                $post->getMorphClass(),
                (int) $post->id
            )) {
                return;
            }

            ContentBoost::query()->create([
                'actor_user_id' => $actor->id,
                'user_post_id' => $post->id,
                'weight' => $weight,
                'points_spent' => $cost,
                'starts_at' => $starts,
                'ends_at' => $ends,
            ]);

            $sum = ContentBoost::sumActiveWeightForPost((int) $post->id);
            $post->forceFill([
                'boost_weight' => $sum,
                'last_boost_at' => now(),
            ])->save();

            $ok = true;
        });

        if (! $ok) {
            return ['ok' => false, 'message' => '积分不足或操作失败'];
        }

        self::notifyAuthorAndActor($actor, $post, $cost);
        self::notifyRandomUsers($actor, $post);

        return ['ok' => true, 'message' => '加热成功'];
    }

    private static function notifyAuthorAndActor(User $actor, UserPost $post, int $cost): void
    {
        $url = route('posts.show', $post);
        $actorName = $actor->name ?? '用户';
        $titleShort = mb_strlen($post->title) > 40 ? mb_substr($post->title, 0, 40).'…' : $post->title;

        $authorId = (int) $post->user_id;
        if ($authorId > 0 && $authorId !== (int) $actor->id) {
            PushInboxDispatcher::send(
                $authorId,
                'boost_received',
                '「'.$actorName.'」为你的投稿加热',
                '《'.$titleShort.'》获得加热（对方消耗 '.$cost.' 积分）。',
                $url
            );
        }

        if (Schema::hasTable('notifications')) {
            InboxNotification::query()->create([
                'user_id' => $actor->id,
                'type' => 'boost_spent',
                'title' => '加热成功',
                'content' => '你已为《'.$titleShort.'》加热，消耗 '.$cost.' 积分。',
                'action_url' => $url,
            ]);
        }
    }

    private static function notifyRandomUsers(User $actor, UserPost $post): void
    {
        $n = PointsRuleConfig::boostRandomNotifyUsers();
        if ($n === 0 || ! Schema::hasTable('users')) {
            return;
        }

        $exclude = array_values(array_unique(array_filter([(int) $actor->id, (int) $post->user_id])));
        $ids = User::query()
            ->where('is_banned', false)
            ->whereNotIn('id', $exclude)
            ->inRandomOrder()
            ->limit($n)
            ->pluck('id')
            ->all();

        $url = route('posts.show', $post);
        $titleShort = mb_strlen($post->title) > 40 ? mb_substr($post->title, 0, 40).'…' : $post->title;
        $actorName = $actor->name ?? '用户';

        foreach ($ids as $uid) {
            PushInboxDispatcher::send(
                (int) $uid,
                'boost_spotlight',
                '社区热门：一篇投稿正在被加热',
                '「'.$actorName.'」加热了《'.$titleShort.'》，来看看。',
                $url
            );
        }
    }

    /**
     * 定时或写操作后校正 boost_weight（过期记录不再计入）。
     */
    public static function refreshBoostWeight(UserPost $post): void
    {
        if (! Schema::hasTable('content_boosts') || ! Schema::hasColumn('user_posts', 'boost_weight')) {
            return;
        }
        $sum = ContentBoost::sumActiveWeightForPost((int) $post->id);
        UserPost::query()->whereKey($post->id)->update(['boost_weight' => $sum]);
    }
}
