<?php

return [
    /** 每日首次登录奖励（0 表示关闭） */
    'login_daily' => (int) env('POINTS_LOGIN_DAILY', 5),

    /** 投稿审核通过奖励作者 */
    'post_approved' => (int) env('POINTS_POST_APPROVED', 20),

    /** 自己的投稿被点赞（作者获得） */
    'post_liked_author' => (int) env('POINTS_POST_LIKED_AUTHOR', 2),

    /** 自己的投稿被收藏（作者获得） */
    'post_favorited_author' => (int) env('POINTS_POST_FAVORITED_AUTHOR', 3),

    /** 自己的投稿收到顶层评论（作者获得） */
    'post_commented_author' => (int) env('POINTS_POST_COMMENTED_AUTHOR', 2),
];
