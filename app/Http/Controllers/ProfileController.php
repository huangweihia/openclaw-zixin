<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2048', 'mimes:jpeg,jpg,png,gif,webp'],
        ]);

        $user = $request->user();
        $path = $request->file('avatar')->store('avatars', 'public');
        $url = asset('storage/'.$path);

        if ($user->avatar && preg_match('#/storage/(.+)$#', (string) $user->avatar, $m)) {
            Storage::disk('public')->delete($m[1]);
        }

        $user->forceFill(['avatar' => $url])->save();

        return back()->with('success', '头像已更新');
    }

    public function updateName(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:50'],
        ]);

        $request->user()->forceFill(['name' => $data['name']])->save();

        return back()->with('success', '昵称已保存');
    }

    public function updateBio(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bio' => ['nullable', 'string', 'max:500'],
        ]);

        $request->user()->forceFill([
            'bio' => $data['bio'] !== null && $data['bio'] !== '' ? $data['bio'] : null,
        ])->save();

        return back()->with('success', '简介已保存');
    }

    public function sendEmailChangeCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = $request->user();
        $email = strtolower($data['email']);

        if (strcasecmp($email, $user->email) === 0) {
            throw ValidationException::withMessages(['email' => '新邮箱不能与当前邮箱相同']);
        }

        if (User::query()->where('email', $email)->exists()) {
            throw ValidationException::withMessages(['email' => '该邮箱已被其他账号使用']);
        }

        $rateKey = 'profile_email_rate_'.$user->id;
        if (Cache::has($rateKey)) {
            throw ValidationException::withMessages(['email' => '发送过于频繁，请 60 秒后再试']);
        }

        $code = (string) random_int(100000, 999999);
        $cacheKey = 'profile_email_code_'.$user->id.'_'.sha1($email);

        if (app()->environment('local')) {
            $smtpPass = (string) config('mail.mailers.smtp.password');
            if ($smtpPass === '' || strcasecmp($smtpPass, 'your_auth_code') === 0) {
                throw ValidationException::withMessages([
                    'email' => '请在 .env 配置可用的 SMTP（MAIL_PASSWORD 等），与注册验证码一致。',
                ]);
            }
        }

        try {
            Mail::raw(
                "你正在将 OpenClaw 智信账号绑定邮箱修改为：{$email}\n验证码：{$code}（5 分钟内有效）\n如非本人操作请忽略。",
                function ($message) use ($email) {
                    $message->to($email)->subject('OpenClaw 智信 - 修改邮箱验证码');
                }
            );
        } catch (\Throwable $e) {
            Log::warning('profile email change code failed', [
                'user_id' => $user->id,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            throw ValidationException::withMessages([
                'email' => '验证码邮件发送失败，请检查邮箱或稍后再试。',
            ]);
        }

        Cache::put($cacheKey, $code, now()->addMinutes(5));
        Cache::put($rateKey, 1, now()->addSeconds(60));

        return response()->json(['message' => '验证码已发送至新邮箱']);
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'verification_code' => ['required', 'digits:6'],
            'current_password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $email = strtolower($data['email']);

        if (strcasecmp($email, $user->email) === 0) {
            return back()->withErrors(['email' => '新邮箱不能与当前邮箱相同'])->withInput();
        }

        if (User::query()->where('email', $email)->exists()) {
            return back()->withErrors(['email' => '该邮箱已被占用'])->withInput();
        }

        $cacheKey = 'profile_email_code_'.$user->id.'_'.sha1($email);
        $cached = Cache::get($cacheKey);
        if (! $cached || $cached !== $data['verification_code']) {
            return back()->withErrors(['verification_code' => '验证码错误或已过期'])->withInput();
        }

        $user->forceFill([
            'email' => $email,
            'email_verified_at' => now(),
        ])->save();

        Cache::forget($cacheKey);

        return back()->with('success', '登录邮箱已更新');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $request->user()->forceFill([
            'password' => $request->input('password'),
        ])->save();

        return redirect()->route('dashboard.edit')->with('success', '密码已修改，请牢记新密码');
    }

    /**
     * 绑定企业微信 userid（后期 Webhook 推送用；当前为手动填写，OAuth 可后续接）。
     */
    public function updateWecom(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'enterprise_wechat_id' => ['nullable', 'string', 'max:128'],
        ]);

        $request->user()->forceFill([
            'enterprise_wechat_id' => $data['enterprise_wechat_id'] !== null && $data['enterprise_wechat_id'] !== ''
                ? $data['enterprise_wechat_id']
                : null,
        ])->save();

        return back()->with('wecom_saved', true);
    }

    public function updatePrivacyMode(Request $request): RedirectResponse
    {
        $request->user()->forceFill([
            'privacy_mode' => $request->boolean('privacy_mode'),
        ])->save();

        return back()->with('privacy_saved', true);
    }
}
