<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\PointsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    public function create(Request $request)
    {
        return view('auth.register', [
            'trial' => $request->query('trial'),
        ]);
    }

    public function sendCode(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $data['email'];

        if (User::query()->where('email', $email)->exists()) {
            throw ValidationException::withMessages(['email' => '该邮箱已注册，请直接登录']);
        }

        $rateKey = 'email_code_rate_' . sha1($email);
        if (Cache::has($rateKey)) {
            throw ValidationException::withMessages(['email' => '发送过于频繁，请稍后再试']);
        }

        $code = (string) random_int(100000, 999999);

        if (app()->environment('local')) {
            $smtpPass = (string) config('mail.mailers.smtp.password');
            if ($smtpPass === '' || strcasecmp($smtpPass, 'your_auth_code') === 0) {
                throw ValidationException::withMessages([
                    'email' => '请在 .env 将 MAIL_PASSWORD 填写为 QQ 邮箱的 SMTP 授权码（不能使用占位符 your_auth_code），与能发信的 ai-side-laravel-max 配置一致；修改后执行：php artisan config:clear。',
                ]);
            }
        }

        try {
            Mail::raw("你的 OpenClaw 智信验证码是：{$code}（5 分钟内有效）", function ($message) use ($email) {
                $message->to($email)->subject('OpenClaw 智信 - 注册验证码');
            });
        } catch (\Throwable $e) {
            Log::warning('send register email code failed', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw ValidationException::withMessages([
                'email' => '验证码邮件发送失败，请检查 SMTP 配置（如 QQ 邮箱需开启 SMTP、使用授权码）或稍后再试。',
            ]);
        }

        Cache::put('email_code_' . sha1($email), $code, now()->addMinutes(5));
        Cache::put($rateKey, 1, now()->addSeconds(60));

        Log::info('register email code sent', ['email' => $email]);

        return new JsonResponse(['message' => '验证码已发送']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'verification_code' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'name' => ['nullable', 'string', 'min:2', 'max:50'],
        ]);

        $email = $data['email'];

        if (User::query()->where('email', $email)->exists()) {
            return back()->withInput($request->except('password', 'password_confirmation'))->withErrors(['email' => '该邮箱已注册']);
        }

        $cacheKey = 'email_code_' . sha1($email);
        $cached = Cache::get($cacheKey);
        if (!$cached || $cached !== $data['verification_code']) {
            return back()->withInput($request->except('password', 'password_confirmation'))->withErrors(['verification_code' => '验证码错误或已过期']);
        }

        $name = $data['name'] ?: (str_contains($email, '@') ? explode('@', $email)[0] : '用户');

        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $data['password'],
            'email_verified_at' => now(),
        ]);

        $this->applyRegisterGift($user);
        $this->applyRegisterPoints($user);

        Cache::forget($cacheKey);

        Auth::login($user);
        $request->session()->regenerate();

        $this->sendWelcomeEmail($user);

        return redirect('/')->with('success', '注册成功');
    }

    /**
     * 注册成功后发送欢迎邮件（读取 email_templates.key = register_welcome；失败仅记日志）。
     */
    private function sendWelcomeEmail(User $user): void
    {
        $tpl = EmailTemplate::query()
            ->where('key', 'register_welcome')
            ->where('is_active', true)
            ->first();

        $siteName = 'OpenClaw 智信';
        $loginUrl = url('/login');
        $vars = [
            '{{user_name}}' => $user->name,
            '{{site_name}}' => $siteName,
            '{{login_url}}' => $loginUrl,
        ];

        $subject = $tpl ? strtr($tpl->subject, $vars) : '欢迎加入 '.$siteName;
        $html = $tpl ? strtr($tpl->content, $vars) : '<p>您好，<strong>'.$user->name.'</strong>，欢迎注册 '.$siteName.'。</p>';
        $plain = $tpl && $tpl->plain_text ? strtr($tpl->plain_text, $vars) : strip_tags($html);

        try {
            Mail::html($html, function ($message) use ($user, $subject, $plain) {
                $message->to($user->email)->subject($subject);
                $message->text($plain);
            });
        } catch (\Throwable $e) {
            Log::warning('welcome email failed after register', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 按后台「系统与站点」中的 register_gift_* 配置赠送会员天数（闭环）。
     */
    private function applyRegisterGift(User $user): void
    {
        if (SiteSetting::getValue('register_gift_enabled', '0') !== '1') {
            return;
        }
        $days = max(0, (int) SiteSetting::getValue('register_gift_days', '0'));
        $role = (string) SiteSetting::getValue('register_gift_role', 'vip');
        if ($days <= 0 || ! in_array($role, ['vip', 'svip'], true)) {
            return;
        }
        $user->forceFill([
            'role' => $role,
            'subscription_ends_at' => now()->addDays($days),
        ])->save();
    }

    /**
     * 注册赠送积分（site_settings.register_points_bonus，与 points 流水表闭环）。
     */
    private function applyRegisterPoints(User $user): void
    {
        $bonus = max(0, (int) SiteSetting::getValue('register_points_bonus', '0'));
        if ($bonus <= 0) {
            return;
        }
        PointsService::earn($user, $bonus, 'register', '新用户注册奖励');
    }
}

