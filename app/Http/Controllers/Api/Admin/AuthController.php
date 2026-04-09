<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        $remember = (bool) ($request->boolean('remember'));

        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $remember)) {
            throw ValidationException::withMessages([
                'email' => ['邮箱或密码错误'],
            ]);
        }

        /** @var User $user */
        $user = $request->user();
        if ($user->is_banned) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => ['该账号已被禁用'],
            ]);
        }

        if ($user->role !== 'admin') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => ['仅限管理员登录'],
            ]);
        }

        // 抽离后台用户：首次管理员登录自动创建 admin_users 记录（兼容不破坏现有账号）
        $adminProfile = $user->adminUser;
        if (! $adminProfile) {
            $adminProfile = AdminUser::create([
                'user_id' => $user->id,
                'display_name' => $user->name ?: null,
                'is_active' => true,
                'is_super' => false,
            ]);
        }

        if (! $adminProfile->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => ['管理员账号已停用'],
            ]);
        }

        $request->session()->regenerate();

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        $adminProfile->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        return new JsonResponse([
            'user' => $this->userPayload($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return new JsonResponse(['ok' => true]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return new JsonResponse(['user' => $this->userPayload($user)]);
    }

    private function userPayload(User $user): array
    {
        $adminProfile = $user->adminUser;
        $adminRoles = $user->adminRoles()->get(['id', 'name', 'key'])->toArray();
        $adminPermissions = $user->adminPermissions();
        if ($adminProfile?->is_super) {
            $adminPermissions = ['*'];
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'admin_profile' => $adminProfile
                ? [
                    'id' => $adminProfile->id,
                    'display_name' => $adminProfile->display_name,
                    'is_active' => (bool) $adminProfile->is_active,
                    'is_super' => (bool) $adminProfile->is_super,
                ]
                : null,
            'admin_roles' => $adminRoles,
            'admin_permissions' => $adminPermissions,
        ];
    }
}
