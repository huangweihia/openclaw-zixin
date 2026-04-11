<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SkinConfig;
use App\Support\AdminUniqueCode;
use App\Support\SkinCssVariables;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SkinConfigController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'skins' => SkinConfig::query()->orderByDesc('sort')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateSkinPayload($request);
        $data['code'] = AdminUniqueCode::slug($data['name'], SkinConfig::class, 'code', null, 'theme');
        $row = SkinConfig::query()->create($data);

        return response()->json(['message' => '已创建', 'skin' => $row], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = SkinConfig::query()->findOrFail($id);
        $data = $this->validateSkinPayload($request);
        unset($data['code']);
        $row->fill($data)->save();

        return response()->json(['message' => '已更新', 'skin' => $row->fresh()]);
    }

    public function destroy(int $id): JsonResponse
    {
        SkinConfig::query()->whereKey($id)->delete();

        return response()->json(['message' => '已删除']);
    }

    /**
     * 创建与更新共用；不包含 code（创建时由程序生成，更新时不允许改）。
     */
    private function validateSkinPayload(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'preview_image' => ['nullable', 'string', 'max:255'],
            'css_variables' => ['required', 'array'],
            'type' => ['required', Rule::in(['free', 'vip', 'svip'])],
            'sort' => ['sometimes', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        SkinCssVariables::assertRequiredPresent($data['css_variables']);
        $data['css_variables'] = SkinCssVariables::normalize($data['css_variables']);

        return $data;
    }
}
