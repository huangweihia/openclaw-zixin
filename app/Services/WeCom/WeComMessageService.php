<?php

namespace App\Services\WeCom;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * 企业微信「应用消息」文本推送（需用户已绑定 enterprise_wechat_id = 企微 userid）。
 *
 * @see https://developer.work.weixin.qq.com/document/path/90236
 */
class WeComMessageService
{
    public function __construct(
        private WeComOAuthService $oauth,
    ) {}

    /**
     * @return array{ok: bool, message?: string}
     */
    public function sendTextToUser(string $enterpriseUserId, string $content): array
    {
        if (! $this->oauth->isConfigured()) {
            return ['ok' => false, 'message' => '企业微信未配置。'];
        }

        $token = $this->oauth->getAccessToken();
        if ($token === null) {
            return ['ok' => false, 'message' => '无法获取 access_token。'];
        }

        $agentId = (int) config('wecom.agent_id');
        $payload = [
            'touser' => $enterpriseUserId,
            'msgtype' => 'text',
            'agentid' => $agentId,
            'text' => ['content' => $content],
        ];

        $res = Http::timeout(15)
            ->asJson()
            ->post('https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token='.$token, $payload)
            ->json();

        if (! is_array($res)) {
            return ['ok' => false, 'message' => '发送接口无响应。'];
        }

        if (($res['errcode'] ?? -1) !== 0) {
            Log::warning('wecom.message.send', ['res' => $res]);

            return ['ok' => false, 'message' => (string) ($res['errmsg'] ?? '发送失败')];
        }

        return ['ok' => true];
    }
}
