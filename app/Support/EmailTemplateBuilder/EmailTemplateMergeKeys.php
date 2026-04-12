<?php

namespace App\Support\EmailTemplateBuilder;

/**
 * 拖拽「插入占位符」下拉里可选的键（与发送逻辑 strtr 一致）。
 */
final class EmailTemplateMergeKeys
{
    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            'site_name' => '站点名称 site_name',
            'date' => '日期 date',
            'article_list_html' => '文章列表 HTML article_list_html',
            'digest_article_title' => '头条文章标题 digest_article_title',
            'digest_article_summary' => '头条文章摘要 digest_article_summary',
            'digest_article_url' => '头条文章链接 digest_article_url',
            'user_name' => '用户昵称 user_name',
            'email' => '收件邮箱 email',
            'topics_list' => '已选订阅主题（中文）topics_list',
            'topic_schedule_summary' => '发送时间偏好 topic_schedule_summary',
            'manage_url' => '管理订阅页 manage_url',
            'unsubscribe_url' => '一键退订链接 unsubscribe_url',
            'is_new_subscription' => '订阅提示语 is_new_subscription',
            'body_html' => '通知/推广正文 HTML body_html',
        ];
    }
}
