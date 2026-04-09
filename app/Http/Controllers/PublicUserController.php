<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PublicUserController extends Controller
{
    public function snippet(User $user): JsonResponse
    {
        $roleLabel = match ($user->role) {
            'admin' => '管理员',
            'svip' => 'SVIP',
            'vip' => 'VIP',
            default => '用户',
        };

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar,
            'bio' => $user->bio,
            'role_label' => $roleLabel,
            'is_self' => Auth::check() && (int) Auth::id() === (int) $user->id,
        ]);
    }
}
