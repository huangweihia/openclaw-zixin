<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SkinConfig;
use App\Models\UserSkin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SkinController extends Controller
{
    /**
     * 获取所有启用的皮肤列表
     */
    public function index(): JsonResponse
    {
        $skins = SkinConfig::where('is_active', true)
            ->orderBy('sort')
            ->get(['id', 'name', 'code', 'description', 'css_variables', 'type']);

        return response()->json([
            'success' => true,
            'data' => $skins,
        ]);
    }

    /**
     * 获取当前用户的皮肤设置
     */
    public function show(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            // 未登录用户返回默认皮肤
            $defaultSkin = SkinConfig::where('is_active', true)
                ->where('type', 'free')
                ->orderByDesc('sort')
                ->first(['id', 'name', 'code', 'css_variables']);

            return response()->json([
                'success' => true,
                'data' => [
                    'current_skin' => $defaultSkin,
                    'is_customized' => false,
                ],
            ]);
        }

        $userSkin = UserSkin::where('user_id', $user->id)
            ->with('skinConfig')
            ->first();

        if (!$userSkin || !$userSkin->skinConfig) {
            $defaultSkin = SkinConfig::where('is_active', true)
                ->where('type', 'free')
                ->orderByDesc('sort')
                ->first(['id', 'name', 'code', 'css_variables']);

            return response()->json([
                'success' => true,
                'data' => [
                    'current_skin' => $defaultSkin,
                    'is_customized' => false,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'current_skin' => $userSkin->skinConfig,
                'is_customized' => true,
                'activated_at' => $userSkin->activated_at,
            ],
        ]);
    }

    /**
     * 更新用户皮肤设置
     */
    public function update(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => '请先登录',
            ], 401);
        }

        $request->validate([
            'skin_code' => 'required|string|exists:skin_configs,code',
        ]);

        $skinConfig = SkinConfig::where('code', $request->skin_code)
            ->where('is_active', true)
            ->first();

        if (!$skinConfig) {
            return response()->json([
                'success' => false,
                'message' => '皮肤不存在或未启用',
            ], 404);
        }

        $role = strtolower((string) ($user->role ?? 'user'));
        $isVip = in_array($role, ['vip', 'svip', 'admin'], true);
        $isSvip = in_array($role, ['svip', 'admin'], true);
        if ($skinConfig->type === 'vip' && ! $isVip) {
            return response()->json([
                'success' => false,
                'message' => '该皮肤仅限 VIP 用户使用',
            ], 403);
        }
        if ($skinConfig->type === 'svip' && ! $isSvip) {
            return response()->json([
                'success' => false,
                'message' => '该皮肤仅限 SVIP 用户使用',
            ], 403);
        }

        UserSkin::updateOrCreate(
            ['user_id' => $user->id],
            [
                'skin_id' => $skinConfig->id,
                'activated_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => '皮肤已更新',
            'data' => [
                'skin_code' => $skinConfig->code,
                'skin_name' => $skinConfig->name,
            ],
        ]);
    }

    /**
     * 重置为默认皮肤
     */
    public function destroy(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => '请先登录',
            ], 401);
        }

        UserSkin::where('user_id', $user->id)->delete();

        return response()->json([
            'success' => true,
            'message' => '已重置为默认皮肤',
        ]);
    }
}
