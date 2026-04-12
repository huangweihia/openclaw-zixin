<?php

return [
    /** 每次加热消耗积分 */
    'points_per_boost' => (int) env('BOOST_POINTS', 100),

    /** 加热有效时长（小时） */
    'window_hours' => (int) env('BOOST_WINDOW_HOURS', 72),

    /**
     * 写入 content_boosts.weight（用于排序/猜你喜欢），可与 points 成比例。
     * weight = max(1, round(points_spent * ratio))
     */
    'weight_per_point_ratio' => (float) env('BOOST_WEIGHT_RATIO', 0.1),

    /** 加热后随机通知其他用户数（站内推送 + 同步到通知中心） */
    'random_notify_users' => (int) env('BOOST_RANDOM_NOTIFY', 15),

    /** 同一用户同一天对同一投稿最多加热次数 */
    'max_boosts_per_actor_per_post_per_day' => (int) env('BOOST_DAILY_CAP_PER_POST', 3),
];
