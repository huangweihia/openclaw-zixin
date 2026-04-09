<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

/**
 * 初始化 QQ 邮箱（SMTP HTML）与企业微信文本模版，供后台「邮件模板」与后续 Webhook 引用。
 */
class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => '注册欢迎（QQ 邮箱 HTML）',
                'key' => 'register_welcome',
                'subject' => '欢迎加入 OpenClaw 智信',
                'content' => <<<'HTML'
<p>您好，<strong>{{user_name}}</strong>，</p>
<p>您的账号已在 <strong>{{site_name}}</strong> 注册成功。</p>
<p>登录地址：<a href="{{login_url}}">{{login_url}}</a></p>
<p>如有疑问请回复本邮件或联系站点管理员。</p>
<p style="color:#64748b;font-size:12px;">本邮件由系统自动发送，请勿直接回复。</p>
HTML
                ,
                'plain_text' => "您好 {{user_name}}，\n\n您在 {{site_name}} 的账号已注册成功。\n登录：{{login_url}}\n",
                'variables' => ['user_name', 'site_name', 'login_url'],
                'is_active' => true,
            ],
            [
                'name' => '企业微信推送默认文本',
                'key' => 'wecom_default_text',
                'subject' => '（企业微信通道不使用邮件主题）',
                'content' => <<<'HTML'
【{{site_name}}】通知

您好 {{user_name}}：

{{message_body}}

—— 发送时间：{{sent_at}}
HTML
                ,
                'plain_text' => "【{{site_name}}】\n\n{{user_name}}，\n{{message_body}}\n\n{{sent_at}}",
                'variables' => ['site_name', 'user_name', 'message_body', 'sent_at'],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $row) {
            EmailTemplate::query()->updateOrCreate(
                ['key' => $row['key']],
                $row
            );
        }
    }
}
