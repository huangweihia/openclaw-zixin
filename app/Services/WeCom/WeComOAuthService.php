<?php

namespace App\Services\WeCom;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class WeComOAuthService
{
    public function isConfigured(): bool
    {
        $c = config('wecom');

        return $c['corp_id'] !== '' && $c['agent_id'] !== '' && $c['agent_secret'] !== '';
    }

    public function buildAuthorizeUrl(int $userId): string
    {
        $state = Crypt::encryptString(json_encode(['uid' => $userId, 't' => time()], JSON_THROW_ON_ERROR));
        $redirect = $this->oauthRedirectUri();
        $agentId = (int) config('wecom.agent_id');
        $corpId = config('wecom.corp_id');

        $query = http_build_query([
            'appid' => $corpId,
            'redirect_uri' => $redirect,
            'response_type' => 'code',
            'scope' => 'snsapi_base',
            'state' => $state,
            'agentid' => $agentId,
        ]);

        return 'https://open.weixin.qq.com/connect/oauth2/authorize?'.$query.'#wechat_redirect';
    }

    public function oauthRedirectUri(): string
    {
        $override = (string) config('wecom.oauth_redirect_uri');

        return $override !== '' ? $override : URL::route('wecom.oauth.callback', [], true);
    }

    /**
     * 使用 OAuth code 换取企业成员 userid（非 openid）。
     *
     * @return array{ok: bool, userid?: string, message?: string}
     */
    public function resolveUserIdFromCode(string $code): array
    {
        $token = $this->getAccessToken();
        if ($token === null) {
            return ['ok' => false, 'message' => '无法获取企业微信 access_token，请检查 WECOM_AGENT_SECRET。'];
        }

        $res = Http::timeout(15)
            ->get('https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo', [
                'access_token' => $token,
                'code' => $code,
            ])
            ->json();

        if (! is_array($res)) {
            return ['ok' => false, 'message' => '企业微信接口无有效响应。'];
        }

        if (($res['errcode'] ?? 0) !== 0) {
            Log::warning('wecom.getuserinfo', ['res' => $res]);

            return ['ok' => false, 'message' => (string) ($res['errmsg'] ?? 'getuserinfo 失败')];
        }

        $userid = $res['UserId'] ?? $res['userid'] ?? null;
        if (! is_string($userid) || $userid === '') {
            return ['ok' => false, 'message' => '未返回企业成员 userid（可能为非企业成员扫码）。'];
        }

        return ['ok' => true, 'userid' => $userid];
    }

    /**
     * 应用级 access_token，缓存略短于 7200 秒。
     */
    public function getAccessToken(): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $corpId = config('wecom.corp_id');
        $secret = config('wecom.agent_secret');
        $cacheKey = 'wecom:access_token:'.$corpId.':'.md5($secret);

        return Cache::remember($cacheKey, 7000, function () use ($corpId, $secret) {
            $res = Http::timeout(15)
                ->get('https://qyapi.weixin.qq.com/cgi-bin/gettoken', [
                    'corpid' => $corpId,
                    'corpsecret' => $secret,
                ])
                ->json();

            if (! is_array($res) || ($res['errcode'] ?? -1) !== 0 || empty($res['access_token'])) {
                Log::warning('wecom.gettoken', ['res' => $res]);

                return null;
            }

            return (string) $res['access_token'];
        });
    }
}
