<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserAdminController extends Controller
{
    public function index(Request $request): View
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

        return view('admin.users.index', [
            'users' => $users,
            'q' => $q,
        ]);
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['user', 'vip', 'svip', 'admin'])],
        ]);

        if ($user->id === $request->user()->id && $data['role'] !== 'admin') {
            return back()->with('error', '不能取消自己的管理员角色。');
        }

        $user->forceFill([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
        ])->save();

        return redirect()->route('admin.users.index')->with('success', '已更新用户「'.$user->name.'」');
    }

    public function disable(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', '不能禁用当前登录账号。');
        }

        if ($user->role === 'admin') {
            return back()->with('error', '请先取消该用户的管理员角色再禁用。');
        }

        $user->forceFill(['is_banned' => true])->save();

        return back()->with('success', '已禁用用户「'.$user->name.'」，其将无法登录。');
    }

    public function enable(User $user): RedirectResponse
    {
        $user->forceFill(['is_banned' => false])->save();

        return back()->with('success', '已解除禁用：「'.$user->name.'」');
    }

    public function updateVip(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'plan' => ['required', Rule::in(['monthly', 'yearly', 'lifetime'])],
            'days' => ['required', 'integer', 'min:1', 'max:3650'],
        ]);

        $expires = match ($data['plan']) {
            'lifetime' => now()->addYears(50),
            'monthly' => now()->addDays($data['days']),
            'yearly' => now()->addDays($data['days']),
        };

        $user->forceFill([
            'role' => 'vip',
            'subscription_ends_at' => $expires,
        ])->save();

        DB::table('subscriptions')->insert([
            'user_id' => $user->id,
            'plan' => $data['plan'],
            'amount' => 0,
            'status' => 'active',
            'started_at' => now(),
            'expires_at' => $expires,
            'payment_id' => 'admin-grant-'.uniqid(),
            'payment_method' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', '已为「'.$user->name.'」开通/续期 VIP。');
    }
}
