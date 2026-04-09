<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $users = User::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($query) use ($q) {
                    $query->where('email', 'like', '%'.$q.'%')
                        ->orWhere('name', 'like', '%'.$q.'%');
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return response()->json($users);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'user' => $this->serializeUser($user),
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['user', 'vip', 'svip', 'admin'])],
        ]);

        if ($user->id === $request->user()->id && $data['role'] !== 'admin') {
            return response()->json(['message' => '不能取消自己的管理员角色。'], 422);
        }

        $user->forceFill([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
        ])->save();

        return response()->json([
            'message' => '已更新用户「'.$user->name.'」',
            'user' => $this->serializeUser($user->fresh()),
        ]);
    }

    public function disable(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => '不能禁用当前登录账号。'], 422);
        }

        if ($user->role === 'admin') {
            return response()->json(['message' => '请先取消该用户的管理员角色再禁用。'], 422);
        }

        $user->forceFill(['is_banned' => true])->save();

        return response()->json([
            'message' => '已禁用用户「'.$user->name.'」，其将无法登录。',
            'user' => $this->serializeUser($user->fresh()),
        ]);
    }

    public function enable(User $user): JsonResponse
    {
        $user->forceFill(['is_banned' => false])->save();

        return response()->json([
            'message' => '已解除禁用：「'.$user->name.'」',
            'user' => $this->serializeUser($user->fresh()),
        ]);
    }

    public function clearEnterpriseWechat(User $user): JsonResponse
    {
        if (! Schema::hasColumn('users', 'enterprise_wechat_id')) {
            return response()->json(['message' => '当前库无企业微信字段'], 422);
        }
        $user->forceFill(['enterprise_wechat_id' => null])->save();

        return response()->json([
            'message' => '已清除该用户的企业微信绑定标识',
            'user' => $this->serializeUser($user->fresh()),
        ]);
    }

    /**
     * 后台会员闭环：开通 VIP / SVIP 或清除付费身份（对齐功能清单 23）。
     */
    public function updateMembership(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['grant_vip', 'grant_svip', 'clear'])],
            'plan' => ['required_if:action,grant_vip,grant_svip', Rule::in(['monthly', 'yearly', 'lifetime'])],
            'days' => ['required_if:action,grant_vip,grant_svip', 'integer', 'min:1', 'max:3650'],
        ]);

        if (in_array($data['action'], ['grant_vip', 'grant_svip'], true) && $user->role === 'admin') {
            return response()->json(['message' => '请先取消该用户的管理员角色后再开通会员。'], 422);
        }

        if ($data['action'] === 'clear') {
            $patch = ['subscription_ends_at' => null];
            if (in_array($user->role, ['vip', 'svip'], true)) {
                $patch['role'] = 'user';
            }
            $user->forceFill($patch)->save();

            return response()->json([
                'message' => '已清除「'.$user->name.'」的会员权益与到期时间。',
                'user' => $this->serializeUser($user->fresh()),
            ]);
        }

        $expires = match ($data['plan']) {
            'lifetime' => now()->addYears(50),
            'monthly' => now()->addDays($data['days']),
            'yearly' => now()->addDays($data['days']),
        };

        $role = $data['action'] === 'grant_svip' ? 'svip' : 'vip';

        $user->forceFill([
            'role' => $role,
            'subscription_ends_at' => $expires,
        ])->save();

        $suffix = $data['action'] === 'grant_svip' ? 'svip' : 'vip';
        DB::table('subscriptions')->insert([
            'user_id' => $user->id,
            'plan' => $data['plan'],
            'amount' => 0,
            'status' => 'active',
            'started_at' => now(),
            'expires_at' => $expires,
            'payment_id' => 'admin-grant-'.$suffix.'-'.uniqid(),
            'payment_method' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $label = $role === 'svip' ? 'SVIP' : 'VIP';

        return response()->json([
            'message' => '已为「'.$user->name.'」开通/续期 '.$label.'。',
            'user' => $this->serializeUser($user->fresh()),
        ]);
    }

    private function serializeUser(User $user): array
    {
        $wecom = null;
        if (Schema::hasColumn('users', 'enterprise_wechat_id')) {
            $raw = $user->enterprise_wechat_id;
            $wecom = [
                'bound' => $raw !== null && $raw !== '',
                'masked' => $raw ? '已绑定（'.mb_substr((string) $raw, 0, 4).'***）' : null,
            ];
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_banned' => (bool) $user->is_banned,
            'enterprise_wechat' => $wecom,
            'subscription_ends_at' => $user->subscription_ends_at?->toIso8601String(),
            'last_login_at' => $user->last_login_at?->toIso8601String(),
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }
}
