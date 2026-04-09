<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SiteSettingController extends Controller
{
    /**
     * 预置键的默认值（数据库无记录时使用）
     *
     * @var array<string, string>
     */
    private const DEFAULTS = [
        'site_name' => 'OpenClaw 智信',
        'site_slogan' => 'OpenClaw + AI 智能体：把分散的信息差转化为可交付的咨询洞察',
        'site_description' => '',
        'site_logo_url' => '',
        'contact_email' => '',
        'contact_wechat' => '',
        'footer_notice' => '',
        'analytics_note' => '',
        // 注册与会员（闭环：RegisterController 读取）
        'register_gift_enabled' => '0',
        'register_gift_role' => 'vip',
        'register_gift_days' => '0',
        'register_points_bonus' => '100',
        // 邮件批处理时间窗（供调度/队列任务读取，0–23）
        'mail_batch_enabled' => '1',
        'mail_batch_start_hour' => '9',
        'mail_batch_end_hour' => '22',
        // 价格营销（前台 VIP/SVIP 区读取；截止时间为 ISO8601 或空）
        'pricing_vip_deadline' => '',
        'pricing_svip_deadline' => '',
        'pricing_vip_seats' => '200',
        'pricing_svip_seats' => '50',
        'pricing_vip_promo' => '限时特惠',
        'pricing_svip_promo' => '早鸟价',
    ];

    public function index(): JsonResponse
    {
        if (! Schema::hasTable('site_settings')) {
            return response()->json([
                'settings' => self::DEFAULTS,
                'degraded' => true,
                'hint' => '请先执行迁移：php artisan migrate（创建 site_settings 表后再保存）',
            ]);
        }

        $stored = SiteSetting::allAsMap();
        $settings = array_merge(self::DEFAULTS, $stored);

        return response()->json(['settings' => $settings]);
    }

    public function update(Request $request): JsonResponse
    {
        if (! Schema::hasTable('site_settings')) {
            return response()->json([
                'message' => '数据库尚未创建 site_settings 表，请先在服务器执行：php artisan migrate',
            ], 503);
        }

        $data = $request->validate([
            'settings' => ['required', 'array'],
            // 允许数字、布尔等 JSON 类型，保存前统一转成字符串（Vue 常把 number 直接 POST）
            'settings.*' => ['nullable'],
        ]);

        foreach ($data['settings'] as $key => $value) {
            if (! is_string($key) || ! preg_match('/^[a-z0-9_.-]{1,120}$/', $key)) {
                continue;
            }
            if ($value === null) {
                SiteSetting::setValue($key, '');

                continue;
            }
            if (is_bool($value)) {
                SiteSetting::setValue($key, $value ? '1' : '0');

                continue;
            }
            if (is_scalar($value)) {
                SiteSetting::setValue($key, (string) $value);

                continue;
            }
            SiteSetting::setValue($key, json_encode($value, JSON_UNESCAPED_UNICODE));
        }

        return $this->index();
    }
}
