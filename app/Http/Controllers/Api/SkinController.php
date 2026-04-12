<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SkinConfig;
use App\Models\User;
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
        $userId = Auth::guard('sanctum')->id() ?? Auth::id();
        $skins = SkinConfig::query()
            ->where('is_active', true)
            ->where(function ($q) use ($userId) {
                $q->where('is_private', false)
                    ->orWhere(function ($inner) use ($userId) {
                        $inner->where('is_private', true)
                            ->where('owner_user_id', $userId ?: 0);
                    });
            })
            ->orderBy('sort')
            ->get(['id', 'name', 'code', 'description', 'css_variables', 'type', 'is_private']);

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
        $user = Auth::guard('sanctum')->user() ?? Auth::user();

        if (!$user) {
            // 未登录用户返回默认皮肤
            $defaultSkin = SkinConfig::where('is_active', true)
                ->where('type', 'free')
                ->where('is_private', false)
                ->orderBy('sort')
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
                ->where('is_private', false)
                ->orderBy('sort')
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
        // API Bearer 走 sanctum 守卫；避免默认 web 守卫下 Auth::user() 为空（小程序换肤 PUT）
        $user = Auth::guard('sanctum')->user() ?? Auth::user();

        if (! $user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => '请先登录',
            ], 401);
        }

        $request->validate([
            'skin_code' => ['nullable', 'string', 'exists:skin_configs,code'],
            'skin_id' => ['nullable', 'integer', 'exists:skin_configs,id'],
        ]);

        if (! $request->filled('skin_code') && ! $request->filled('skin_id')) {
            return response()->json([
                'success' => false,
                'message' => '请提供 skin_code 或 skin_id',
            ], 422);
        }

        $skinConfig = null;
        if ($request->filled('skin_id')) {
            $skinConfig = SkinConfig::query()
                ->where('id', $request->integer('skin_id'))
                ->where('is_active', true)
                ->first();
        } else {
            $skinConfig = SkinConfig::query()
                ->where('code', $request->string('skin_code')->toString())
                ->where('is_active', true)
                ->first();
        }

        if (!$skinConfig) {
            return response()->json([
                'success' => false,
                'message' => '皮肤不存在或未启用',
            ], 404);
        }
        if ((bool) $skinConfig->is_private && (int) $skinConfig->owner_user_id !== (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => '该定制皮肤仅创建者本人可使用',
            ], 403);
        }

        if ($skinConfig->type === 'vip' && ! $user->canAccessVipExclusiveContent()) {
            return response()->json([
                'success' => false,
                'message' => '该皮肤仅限 VIP 用户使用',
            ], 403);
        }
        if ($skinConfig->type === 'svip' && ! $user->canAccessSvipExclusiveContent()) {
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
        $user = Auth::guard('sanctum')->user() ?? Auth::user();

        if (! $user) {
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
