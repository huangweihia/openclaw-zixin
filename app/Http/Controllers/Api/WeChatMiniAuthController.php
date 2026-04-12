<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserRegistrationRewards;
use App\Support\EmailLogWriter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class WeChatMiniAuthController extends Controller
{
    /**
     * 使用 wx.login 返回的 code 换 openid，并签发 Sanctum Token。
     * 首次注册走与邮箱注册相同的「注册赠送会员 / 积分」后台配置；openid 写入 wechat_openid（及兼容 wechat_mini_openid）。
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'max:256'],
            'nickname' => ['required', 'string', 'min:1', 'max:64'],
            'avatar' => ['nullable', 'string', 'max:512'],
        ]);

        $nickname = $this->sanitizeNickname($request->string('nickname')->toString());
        if ($nickname === '') {
            return response()->json(['message' => '请提供有效昵称'], 422);
        }

        $avatarUrl = $this->sanitizeWeChatAvatarUrl($request->input('avatar'));

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

        $user = $this->findUserByWeChatOpenId($openid);

        $isNewUser = false;
        if (! $user) {
            $email = $this->syntheticEmailForOpenId($openid);
            $create = [
                'name' => $nickname,
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'wechat_unionid' => $unionid,
                'role' => 'user',
                'avatar' => $avatarUrl,
            ];
            if (Schema::hasColumn('users', 'wechat_openid')) {
                $create['wechat_openid'] = $openid;
            }
            if (Schema::hasColumn('users', 'wechat_mini_openid')) {
                $create['wechat_mini_openid'] = $openid;
            }
            $user = User::query()->create($create);
            $isNewUser = true;
        } else {
            $patch = [];
            if (Schema::hasColumn('users', 'wechat_openid')) {
                $patch['wechat_openid'] = $openid;
            }
            if (Schema::hasColumn('users', 'wechat_mini_openid')) {
                $patch['wechat_mini_openid'] = $openid;
            }
            if ($unionid !== null && $user->wechat_unionid !== $unionid) {
                $patch['wechat_unionid'] = $unionid;
            }
            $patch['name'] = $nickname;
            if ($avatarUrl !== null) {
                $patch['avatar'] = $avatarUrl;
            }
            if ($patch !== []) {
                $user->forceFill($patch)->save();
            }
        }

        if ($isNewUser) {
            UserRegistrationRewards::applyRegisterGift($user);
            UserRegistrationRewards::applyRegisterPoints($user);
            $user->refresh();
        }

        if ($user->is_banned) {
            return response()->json(['message' => '账号已被限制登录'], 403);
        }

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        $user->refresh();

        $tokenName = 'wechat-mini';
        // 不再「每次登录删光旧 Token」：否则用户换设备登录、或在开发者工具里再次点登录，
        // 会使其它端仍握在手里的 Token 立刻 401，体感像「登录莫名过期」。
        // 改为签发新 Token，并只淘汰最旧的若干条，兼顾多端在线与表体积。
        $plain = $user->createToken($tokenName)->plainTextToken;
        $this->pruneExcessWeChatMiniTokens($user, $tokenName, 15);

        return response()->json([
            'token' => $plain,
            'token_type' => 'Bearer',
            'user' => $this->userPayload($user),
            'stats' => WeChatMiniProfileController::statsFor($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->refresh();

        return response()->json([
            'user' => $this->userPayload($user),
            'stats' => WeChatMiniProfileController::statsFor($user),
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

    /**
     * 小程序账号（占位邮箱）向真实邮箱发送绑定验证码。
     */
    public function bindEmailSendCode(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        if (! str_ends_with((string) $user->email, '@users.openclaw.local')) {
            return response()->json(['message' => '当前账号已绑定真实邮箱'], 422);
        }

        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);
        $email = strtolower(trim($request->string('email')->toString()));

        if (User::query()->where('email', $email)->where('id', '!=', $user->id)->exists()) {
            return response()->json(['message' => '该邮箱已被其他账号使用'], 422);
        }

        $rateKey = 'mini_bind_email_rate_'.sha1($email);
        if (Cache::has($rateKey)) {
            return response()->json(['message' => '发送过于频繁，请稍后再试'], 429);
        }

        $code = (string) random_int(100000, 999999);

        if (app()->environment('local')) {
            $smtpPass = (string) config('mail.mailers.smtp.password');
            if ($smtpPass === '' || strcasecmp($smtpPass, 'your_auth_code') === 0) {
                return response()->json([
                    'message' => '服务端未配置可用 SMTP，无法发送验证码（请配置 MAIL_* 后重试）',
                ], 503);
            }
        }

        try {
            Mail::raw("你的 OpenClaw 智信邮箱绑定验证码是：{$code}（5 分钟内有效）", function ($message) use ($email) {
                $message->to($email)->subject('OpenClaw 智信 - 绑定邮箱验证码');
            });
            EmailLogWriter::sent($user->id, $email, 'OpenClaw 智信 - 绑定邮箱验证码', 'mini_bind_email');
        } catch (\Throwable $e) {
            EmailLogWriter::failed($user->id, $email, 'OpenClaw 智信 - 绑定邮箱验证码', $e->getMessage(), 'mini_bind_email');
            Log::warning('mini bind email send failed', [
                'user_id' => $user->id,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => '验证码邮件发送失败，请检查邮箱或稍后再试'], 502);
        }

        Cache::put('mini_bind_email_code_'.sha1($email), $code, now()->addMinutes(5));
        Cache::put($rateKey, 1, now()->addSeconds(60));

        return response()->json(['message' => '验证码已发送']);
    }

    /**
     * 校验验证码并写入 users.email（仅占位邮箱账号可操作）。
     */
    public function bindEmailVerify(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        if (! str_ends_with((string) $user->email, '@users.openclaw.local')) {
            return response()->json(['message' => '当前账号已绑定真实邮箱'], 422);
        }

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'verification_code' => ['required', 'digits:6'],
        ]);
        $email = strtolower(trim($data['email']));

        if (User::query()->where('email', $email)->where('id', '!=', $user->id)->exists()) {
            return response()->json(['message' => '该邮箱已被其他账号使用'], 422);
        }

        $cacheKey = 'mini_bind_email_code_'.sha1($email);
        $cached = Cache::get($cacheKey);
        if (! $cached || (string) $cached !== (string) $data['verification_code']) {
            return response()->json(['message' => '验证码错误或已过期'], 422);
        }

        $user->forceFill([
            'email' => $email,
            'email_verified_at' => now(),
        ])->save();
        Cache::forget($cacheKey);
        $user->refresh();

        return response()->json([
            'message' => '绑定成功',
            'user' => $this->userPayload($user),
            'stats' => WeChatMiniProfileController::statsFor($user),
        ]);
    }

    private function findUserByWeChatOpenId(string $openid): ?User
    {
        if (Schema::hasColumn('users', 'wechat_openid')) {
            return User::query()
                ->where('wechat_openid', $openid)
                ->when(
                    Schema::hasColumn('users', 'wechat_mini_openid'),
                    fn ($q) => $q->orWhere('wechat_mini_openid', $openid)
                )
                ->first();
        }

        if (Schema::hasColumn('users', 'wechat_mini_openid')) {
            return User::query()->where('wechat_mini_openid', $openid)->first();
        }

        return null;
    }

    private function syntheticEmailForOpenId(string $openid): string
    {
        $local = 'wxm_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $openid);

        return Str::limit($local, 200, '').'@users.openclaw.local';
    }

    private function sanitizeNickname(string $raw): string
    {
        $s = trim(strip_tags($raw));
        $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $s) ?? $s;

        return Str::limit($s, 64, '');
    }

    /**
     * 仅接受微信头像 CDN 等可信 HTTPS 地址，避免任意 URL 写入。
     */
    private function sanitizeWeChatAvatarUrl(mixed $raw): ?string
    {
        if (! is_string($raw)) {
            return null;
        }
        $s = trim($raw);
        if ($s === '' || strlen($s) > 512) {
            return null;
        }
        if (! str_starts_with($s, 'https://')) {
            return null;
        }
        $host = strtolower((string) parse_url($s, PHP_URL_HOST));
        if ($host === '' || $host === 'localhost') {
            return null;
        }
        $allowed =
            str_contains($host, 'qlogo.cn')
            || str_contains($host, 'qpic.cn')
            || str_ends_with($host, '.qq.com')
            || str_contains($host, 'weixin.qq.com')
            || str_contains($host, 'servicewechat.com');
        if (! $allowed) {
            return null;
        }

        return $s;
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
            'email_is_placeholder' => str_ends_with((string) ($user->email ?? ''), '@users.openclaw.local'),
            'role' => $user->role,
            'avatar' => $user->avatar,
            'points_balance' => (int) ($user->points_balance ?? 0),
            'subscription_ends_at' => $user->subscription_ends_at?->toIso8601String(),
            'vip_days_left' => $this->vipDaysLeft($user),
            'role_label' => $this->roleLabel($user),
            /** VIP/SVIP/管理员可读会员专享正文、浏览类 VIP 详情 */
            'has_vip_content_access' => $user->hasMemberMenuPrivileges(),
            /** SVIP 皮肤、高阶定制等（与 SkinController type=svip 一致） */
            'has_svip_privileges' => $user->canAccessSvipExclusiveContent(),
            'is_site_super_admin' => $user->isSiteSuperAdmin(),
        ];
    }

    private function roleLabel(User $user): string
    {
        return match ($user->role) {
            'svip' => 'SVIP',
            'vip' => 'VIP 会员',
            'admin' => '超级管理员',
            default => '免费用户',
        };
    }

    private function vipDaysLeft(User $user): ?int
    {
        if (! $user->subscription_ends_at || ! $user->subscription_ends_at->isFuture()) {
            return null;
        }
        if (! $user->isVip()) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($user->subscription_ends_at->copy()->startOfDay());
    }

    /**
     * 保留同名 Token 最近 $keep 条，删除更旧的（personal_access_tokens）。
     */
    private function pruneExcessWeChatMiniTokens(User $user, string $tokenName, int $keep): void
    {
        if ($keep < 1) {
            return;
        }

        $ids = $user->tokens()
            ->where('name', $tokenName)
            ->orderByDesc('id')
            ->limit($keep)
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        $user->tokens()
            ->where('name', $tokenName)
            ->whereNotIn('id', $ids->all())
            ->delete();
    }
}
