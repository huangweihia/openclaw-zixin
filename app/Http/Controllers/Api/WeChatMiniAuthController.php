<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WeChatMiniAuthController extends Controller
{
    /**
     * 使用 wx.login 返回的 code 换 openid，并签发 Sanctum Token。
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'max:256'],
        ]);

        $appId = (string) config('wechat.mini_app_id', '');
        $secret = (string) config('wechat.mini_app_secret', '');
        if ($appId === '' || $secret === '') {
            return response()->json([
                'message' => '服务端未配置 WECHAT_MINI_APP_ID / WECHAT_MINI_APP_SECRET',
            ], 503);
        }

        $code = $request->string('code')->toString();
        $response = Http::timeout(10)->get('https://api.weixin.qq.com/sns/jscode2session', [
            'appid' => $appId,
            'secret' => $secret,
            'js_code' => $code,
            'grant_type' => 'authorization_code',
        ]);

        if (! $response->successful()) {
            return response()->json(['message' => '微信接口请求失败'], 502);
        }

        $json = $response->json();
        if (! is_array($json)) {
            return response()->json(['message' => '微信接口返回异常'], 502);
        }

        if (isset($json['errcode']) && (int) $json['errcode'] !== 0) {
            return response()->json([
                'message' => (string) ($json['errmsg'] ?? '微信登录失败'),
                'errcode' => $json['errcode'] ?? null,
            ], 422);
        }

        $openid = $json['openid'] ?? null;
        if (! is_string($openid) || $openid === '') {
            return response()->json(['message' => '未获取到 openid'], 422);
        }

        $unionid = isset($json['unionid']) && is_string($json['unionid']) ? $json['unionid'] : null;

        $user = User::query()->where('wechat_mini_openid', $openid)->first();

        if (! $user) {
            $email = $this->syntheticEmailForOpenId($openid);
            $user = User::query()->create([
                'name' => '微信用户',
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'wechat_mini_openid' => $openid,
                'wechat_unionid' => $unionid,
                'role' => 'user',
            ]);
        } else {
            if ($unionid !== null && $user->wechat_unionid !== $unionid) {
                $user->forceFill(['wechat_unionid' => $unionid])->save();
            }
        }

        if ($user->is_banned) {
            return response()->json(['message' => '账号已被限制登录'], 403);
        }

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        $tokenName = 'wechat-mini';
        $user->tokens()->where('name', $tokenName)->delete();
        $plain = $user->createToken($tokenName)->plainTextToken;

        return response()->json([
            'token' => $plain,
            'token_type' => 'Bearer',
            'user' => $this->userPayload($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => $this->userPayload($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $token = $user->currentAccessToken();
        if ($token !== null) {
            $token->delete();
        }

        return response()->json(['ok' => true]);
    }

    private function syntheticEmailForOpenId(string $openid): string
    {
        $local = 'wxm_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $openid);

        return Str::limit($local, 200, '').'@users.openclaw.local';
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'avatar' => $user->avatar,
        ];
    }
}
