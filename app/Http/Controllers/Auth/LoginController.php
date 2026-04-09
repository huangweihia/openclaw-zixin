<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function create(Request $request)
    {
        $returnTo = $this->safeInternalPath($request->query('return'));

        return view('auth.login', [
            'returnTo' => $returnTo,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
            'return' => ['nullable', 'string', 'max:512'],
        ]);

        $remember = (bool) ($credentials['remember'] ?? false);

        if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $remember)) {
            return back()
                ->withInput($request->only('email', 'remember', 'return'))
                ->withErrors(['email' => '邮箱或密码错误']);
        }

        /** @var \App\Models\User $user */
        $user = $request->user();
        if ($user->is_banned) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput($request->only('email', 'remember', 'return'))
                ->withErrors(['email' => '该账号已被禁用，如有疑问请联系客服。']);
        }

        $request->session()->regenerate();

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        $target = $this->safeInternalPath($credentials['return'] ?? $request->query('return'));

        return redirect()->to($target ?: '/')->with('success', '登录成功');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', '已退出登录');
    }

    private function safeInternalPath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }
        $path = urldecode($path);
        if (strlen($path) > 512 || preg_match('/[\r\n\0]/', $path)) {
            return null;
        }
        if (! str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return null;
        }

        return $path;
    }
}
