<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 与 EmailTemplateSeeder 对齐：仅 migrate 时也能得到默认 QQ 欢迎信与企业微信文本模版。
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $rows = [
            [
                'name' => '注册欢迎（QQ 邮箱 HTML）',
                'key' => 'register_welcome',
                'subject' => '欢迎加入 OpenClaw 智信',
                'content' => "<p>您好，<strong>{{user_name}}</strong>，</p>\n<p>您的账号已在 <strong>{{site_name}}</strong> 注册成功。</p>\n<p>登录地址：<a href=\"{{login_url}}\">{{login_url}}</a></p>\n<p>如有疑问请回复本邮件或联系站点管理员。</p>\n<p style=\"color:#64748b;font-size:12px;\">本邮件由系统自动发送，请勿直接回复。</p>",
                'plain_text' => "您好 {{user_name}}，\n\n您在 {{site_name}} 的账号已注册成功。\n登录：{{login_url}}\n",
                'variables' => json_encode(['user_name', 'site_name', 'login_url']),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '企业微信推送默认文本',
                'key' => 'wecom_default_text',
                'subject' => '（企业微信通道不使用邮件主题）',
                'content' => "【{{site_name}}】通知\n\n您好 {{user_name}}：\n\n{{message_body}}\n\n—— 发送时间：{{sent_at}}",
                'plain_text' => "【{{site_name}}】\n\n{{user_name}}，\n{{message_body}}\n\n{{sent_at}}",
                'variables' => json_encode(['site_name', 'user_name', 'message_body', 'sent_at']),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($rows as $row) {
            $match = ['key' => $row['key']];
            DB::table('email_templates')->updateOrInsert($match, $row);
        }
    }

    public function down(): void
    {
        DB::table('email_templates')->whereIn('key', ['register_welcome', 'wecom_default_text'])->delete();
    }
};
