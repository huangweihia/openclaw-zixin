<?php

namespace App\Services\WeChatMini;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * 小程序 client_credential，用于服务端调用 subscribeMessage.send 等。
 */
class AccessTokenService
{
    public function getAccessToken(): ?string
    {
        $appId = trim((string) config('wechat.mini_app_id', ''));
        $secret = trim((string) config('wechat.mini_app_secret', ''));
        if ($appId === '' || $secret === '') {
            return null;
        }

        try {
            return Cache::remember('wechat_mini_client_credential_v1', 7000, function () use ($appId, $secret) {
                $response = Http::timeout(12)->get('https://api.weixin.qq.com/cgi-bin/token', [
                    'grant_type' => 'client_credential',
                    'appid' => $appId,
                    'secret' => $secret,
                ]);
                if (! $response->successful()) {
                    Log::warning('wechat_mini.access_token_http', ['status' => $response->status()]);

                    throw new \RuntimeException('token_http');
                }
                $json = $response->json();
                if (! is_array($json) || empty($json['access_token'])) {
                    Log::warning('wechat_mini.access_token_body', ['json' => $json]);

                    throw new \RuntimeException('token_body');
                }

                return (string) $json['access_token'];
            });
        } catch (\Throwable $e) {
            return null;
        }
    }
}
