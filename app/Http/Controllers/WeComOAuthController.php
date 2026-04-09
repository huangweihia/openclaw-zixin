<?php

namespace App\Http\Controllers;

use App\Services\WeCom\WeComOAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class WeComOAuthController extends Controller
{
    public function redirectToProvider(WeComOAuthService $wecom): RedirectResponse
    {
        if (! $wecom->isConfigured()) {
            return redirect()
                ->route('dashboard.edit')
                ->with('wecom_oauth_error', '企业微信未配置：请在 .env 中设置 WECOM_CORP_ID、WECOM_AGENT_ID、WECOM_AGENT_SECRET，并在企微后台配置 OAuth 回调地址为：'.$wecom->oauthRedirectUri());
        }

        return redirect()->away($wecom->buildAuthorizeUrl((int) Auth::id()));
    }

    public function callback(Request $request, WeComOAuthService $wecom): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()
                ->route('dashboard.edit')
                ->with('wecom_oauth_error', '授权未完成或被用户取消。');
        }

        $code = (string) $request->query('code', '');
        $state = (string) $request->query('state', '');
        if ($code === '' || $state === '') {
            return redirect()
                ->route('dashboard.edit')
                ->with('wecom_oauth_error', '缺少授权参数。');
        }

        try {
            $payload = json_decode(Crypt::decryptString($state), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            Log::notice('wecom.oauth.bad_state', ['e' => $e->getMessage()]);

            return redirect()
                ->route('dashboard.edit')
                ->with('wecom_oauth_error', '授权状态无效，请重新发起绑定。');
        }

        $uid = (int) ($payload['uid'] ?? 0);
        if ($uid !== (int) Auth::id()) {
            return redirect()
                ->route('dashboard.edit')
                ->with('wecom_oauth_error', '登录会话与授权不一致，请重新登录后再试。');
        }

        $resolved = $wecom->resolveUserIdFromCode($code);
        if (! $resolved['ok']) {
            return redirect()
                ->route('dashboard.edit')
                ->with('wecom_oauth_error', $resolved['message'] ?? '绑定失败');
        }

        Auth::user()->forceFill([
            'enterprise_wechat_id' => $resolved['userid'],
        ])->save();

        return redirect()
            ->route('dashboard.edit')
            ->with('wecom_saved', true);
    }
}
