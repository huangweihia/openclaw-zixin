<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 订阅主题与邮件模板映射（后台可改）+ 基础邮件模板数据。
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('email_subscription_topic_templates')) {
            Schema::create('email_subscription_topic_templates', function (Blueprint $table) {
                $table->id();
                $table->string('topic_key', 64)->unique();
                $table->string('template_key', 128);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('email_templates')) {
            return;
        }

        $now = now();
        $templates = [
            [
                'name' => '订阅保存确认',
                'key' => 'sub_saved_confirmation',
                'subject' => '{{is_new_subscription}} — {{site_name}}',
                'content' => '<p>您好 <strong>{{user_name}}</strong>，</p>'
                    .'<p>{{is_new_subscription}}。当前订阅邮箱：<strong>{{email}}</strong></p>'
                    .'<p>已选主题：<strong>{{topics_list}}</strong></p>'
                    .'<p>发送时间偏好：{{topic_schedule_summary}}</p>'
                    .'<p><a href="{{manage_url}}">前往个人中心管理订阅</a></p>'
                    .'<p style="font-size:12px;color:#64748b">退订（无需登录，一键生效）：<a href="{{unsubscribe_url}}">{{unsubscribe_url}}</a></p>',
                'plain_text' => "您好 {{user_name}}，\n\n{{is_new_subscription}}。\n邮箱：{{email}}\n主题：{{topics_list}}\n时间：{{topic_schedule_summary}}\n管理：{{manage_url}}\n退订：{{unsubscribe_url}}\n",
                'variables' => json_encode(['user_name', 'site_name', 'email', 'topics_list', 'topic_schedule_summary', 'manage_url', 'unsubscribe_url', 'is_new_subscription']),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '每日精选（订阅邮件）',
                'key' => 'sub_daily_digest',
                'subject' => '{{site_name}} · 每日内容精选（{{date}}）',
                'content' => '<p><strong>{{site_name}}</strong> 每日精选（{{date}}）：</p>{{article_list_html}}'
                    .'<p style="font-size:12px;color:#64748b">此为自动邮件，可在个人中心调整订阅或点击退订链接。</p>',
                'plain_text' => "{{site_name}} 每日精选 {{date}}\n（请使用 HTML 客户端查看完整列表）\n",
                'variables' => json_encode(['site_name', 'article_list_html', 'date']),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '每周精选（订阅邮件）',
                'key' => 'sub_weekly_digest',
                'subject' => '{{site_name}} · 每周精选（{{date}}）',
                'content' => '<p><strong>{{site_name}}</strong> 每周精选（{{date}}）：</p>{{article_list_html}}'
                    .'<p style="font-size:12px;color:#64748b">此为自动邮件，可在个人中心调整订阅。</p>',
                'plain_text' => "{{site_name}} 每周精选 {{date}}\n",
                'variables' => json_encode(['site_name', 'article_list_html', 'date']),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '系统通知类（订阅批次）',
                'key' => 'sub_notification_digest',
                'subject' => '{{site_name}} · 系统通知摘要',
                'content' => '<p>您好，</p>{{body_html}}<p style="font-size:12px;color:#64748b">发送日：{{date}}</p>',
                'plain_text' => "系统通知摘要 {{date}}\n",
                'variables' => json_encode(['site_name', 'body_html', 'date']),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '活动推广类（订阅批次）',
                'key' => 'sub_promotion_digest',
                'subject' => '{{site_name}} · 活动与推广',
                'content' => '<p>您好，</p>{{body_html}}<p style="font-size:12px;color:#64748b">发送日：{{date}}</p>',
                'plain_text' => "活动推广 {{date}}\n",
                'variables' => json_encode(['site_name', 'body_html', 'date']),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($templates as $row) {
            DB::table('email_templates')->updateOrInsert(
                ['key' => $row['key']],
                $row
            );
        }

        $maps = [
            ['topic_key' => 'subscription_saved', 'template_key' => 'sub_saved_confirmation'],
            ['topic_key' => 'daily', 'template_key' => 'sub_daily_digest'],
            ['topic_key' => 'weekly', 'template_key' => 'sub_weekly_digest'],
            ['topic_key' => 'notification', 'template_key' => 'sub_notification_digest'],
            ['topic_key' => 'promotion', 'template_key' => 'sub_promotion_digest'],
        ];
        foreach ($maps as $m) {
            DB::table('email_subscription_topic_templates')->updateOrInsert(
                ['topic_key' => $m['topic_key']],
                array_merge($m, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('email_subscription_topic_templates')) {
            Schema::dropIfExists('email_subscription_topic_templates');
        }
        if (Schema::hasTable('email_templates')) {
            DB::table('email_templates')->whereIn('key', [
                'sub_saved_confirmation',
                'sub_daily_digest',
                'sub_weekly_digest',
                'sub_notification_digest',
                'sub_promotion_digest',
            ])->delete();
        }
    }
};
