<?php

namespace App\Support\EmailTemplateBuilder;

/**
 * 后台拖拽预览用的占位符示例文案。
 */
final class EmailTemplatePlaceholderSamples
{
    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            'site_name' => 'OpenClaw 智信（预览）',
            'date' => now()->format('Y-m-d'),
            'article_list_html' => '<ul style="margin:8px 0;padding-left:20px;"><li><a href="#">示例文章标题一</a> — 摘要预览</li><li><a href="#">示例文章标题二</a></li></ul>',
            'digest_article_title' => '最新一篇已发布文章标题',
            'digest_article_summary' => '这是用于预览的文章摘要文字，实际发送时会替换为真实内容。',
            'digest_article_url' => url('/'),
            'user_name' => '预览用户',
            'email' => 'preview@example.com',
            'topics_list' => '每日精选、系统通知',
            'topic_schedule_summary' => '每日精选 09:00',
            'manage_url' => url('/dashboard'),
            'unsubscribe_url' => url('/api/email-unsubscribe/sample-token'),
            'is_new_subscription' => '欢迎订阅我们的邮件',
            'body_html' => '<p>系统通知 / 活动推广正文预览</p>',
            'db_articles_title' => '（首篇文章标题）',
            'db_articles_summary' => '（首篇文章摘要）',
            'db_articles_url' => url('/'),
            'db_projects_name' => '（示例项目名称）',
            'db_projects_url' => url('/'),
            'db_users_name' => '（用户姓名）',
            'db_users_email' => 'user@example.com',
        ];
    }
}
