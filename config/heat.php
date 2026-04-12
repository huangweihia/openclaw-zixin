<?php

return [
    /** 登录用户浏览投稿详情，每日每帖仅计一次 */
    'view_once_per_day' => (int) env('HEAT_VIEW_POINTS', 1),

    'like' => (int) env('HEAT_LIKE', 5),
    'favorite' => (int) env('HEAT_FAVORITE', 8),
    /** 顶层评论落在投稿上时给投稿加热度 */
    'root_comment' => (int) env('HEAT_ROOT_COMMENT', 12),

    /** 审核通过一次性加热度 */
    'post_approved' => (int) env('HEAT_POST_APPROVED', 30),
];
