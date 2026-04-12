<?php

namespace App\Services\WeChatMini;

use App\Models\User;
use App\Support\WeChatMiniSubscribeTemplateIds;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * 微信小程序订阅消息（服务端下发，依赖用户曾在客户端 requestSubscribeMessage 授权；一次性模板每次授权可发一条）。
 */
class SubscribeMessageService
{
    public function __construct(
        private AccessTokenService $tokens,
    ) {}

    /**
     * @return array{ok: bool, errcode?: int, errmsg?: string, reason?: string}
     */
    public function sendMembershipExpiryReminder(User $user): array
    {
        $templateId = $this->membershipExpiryTemplateId();
        if ($templateId === '') {
            return ['ok' => false, 'reason' => 'no_template'];
        }

        $openid = $this->miniOpenId($user);
        if ($openid === null || $openid === '') {
            return ['ok' => false, 'reason' => 'no_openid'];
        }

        if ($user->is_banned) {
            return ['ok' => false, 'reason' => 'banned'];
        }

        if (! $user->subscription_ends_at || ! $user->subscription_ends_at->isFuture()) {
            return ['ok' => false, 'reason' => 'no_future_subscription'];
        }

        if (! in_array($user->role, ['vip', 'svip', 'admin'], true)) {
            return ['ok' => false, 'reason' => 'role'];
        }

        $token = $this->tokens->getAccessToken();
        if ($token === null) {
            return ['ok' => false, 'reason' => 'no_access_token'];
        }

        $data = $this->buildExpiryPayload($user);
        if ($data === []) {
            return ['ok' => false, 'reason' => 'empty_data'];
        }

        $page = trim((string) config('wechat.mini_subscribe_jump_page', 'pages/vip/vip'));
        $state = (string) config('wechat.mini_subscribe_miniprogram_state', 'formal');

        $body = [
            'touser' => $openid,
            'template_id' => $templateId,
            'page' => $page !== '' ? $page : 'pages/vip/vip',
            'miniprogram_state' => in_array($state, ['developer', 'trial', 'formal'], true) ? $state : 'formal',
            'lang' => 'zh_CN',
            'data' => $data,
        ];

        $url = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token='.urlencode($token);
        $response = Http::timeout(15)->asJson()->post($url, $body);
        $json = $response->json();
        if (! is_array($json)) {
            Log::warning('wechat_mini.subscribe_send_bad_json', ['body' => $response->body()]);

            return ['ok' => false, 'reason' => 'bad_response'];
        }

        $errcode = (int) ($json['errcode'] ?? -1);
        if ($errcode === 0) {
            return ['ok' => true];
        }

        Log::info('wechat_mini.subscribe_send', [
            'user_id' => $user->id,
            'errcode' => $errcode,
            'errmsg' => $json['errmsg'] ?? '',
        ]);

        return [
            'ok' => false,
            'errcode' => $errcode,
            'errmsg' => (string) ($json['errmsg'] ?? ''),
        ];
    }

    /**
     * @return array<string, array{value: string}>
     */
    private function buildExpiryPayload(User $user): array
    {
        $ends = $user->subscription_ends_at;
        if ($ends === null) {
            return [];
        }

        $daysLeft = (int) now()->startOfDay()->diffInDays($ends->copy()->startOfDay());
        $values = [
            'member_name' => Str::limit($user->name ?: '会员', 20),
            'expires_at' => $ends->timezone(config('app.timezone'))->format('Y-m-d H:i'),
            'days_left' => (string) min(99999, $daysLeft),
            'rights_hint' => Str::limit('请于官网续费享会员权益', 20),
        ];

        /** @var array<string, string> $map 微信模板字段名 => 内部键名 */
        $map = config('wechat.mini_subscribe_expiry_field_map', []);
        if (! is_array($map) || $map === []) {
            $map = [
                'thing1' => 'member_name',
                'time2' => 'expires_at',
                'number3' => 'days_left',
                'thing4' => 'rights_hint',
            ];
        }

        $out = [];
        foreach ($map as $wxKey => $inner) {
            $inner = (string) $inner;
            if (! isset($values[$inner])) {
                continue;
            }
            $v = (string) $values[$inner];
            $out[$wxKey] = ['value' => $v];
        }

        return $out;
    }

    /** 服务端 send 使用的模板 ID；单独 env 优先，否则取 env/站点设置合并列表第一项 */
    public function membershipExpiryTemplateId(): string
    {
        $id = trim((string) config('wechat.mini_subscribe_membership_expiry_template_id', ''));
        if ($id !== '') {
            return $id;
        }
        $list = WeChatMiniSubscribeTemplateIds::forRequestSubscribeMessage();
        $first = trim((string) ($list[0] ?? ''));

        return $first;
    }

    private function miniOpenId(User $user): ?string
    {
        $a = $user->wechat_mini_openid ?? null;
        if (is_string($a) && $a !== '') {
            return $a;
        }
        $b = $user->wechat_openid ?? null;

        return is_string($b) && $b !== '' ? $b : null;
    }
}
