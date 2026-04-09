<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SkinConfig;
use App\Support\AdminUniqueCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SkinConfigController extends Controller
{
    /**
     * 须由运营填写且非空；gradient-primary 不在此列，保存时由程序根据 primary + secondary 自动生成并写入 JSON。
     */
    private const REQUIRED_SKIN_CSS_KEYS = [
        'primary',
        'secondary',
        'bg-primary',
        'text-primary',
    ];

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

        $this->assertRequiredSkinCssVariablesPresent($data['css_variables']);
        $data['css_variables'] = $this->normalizeSkinCssVariables($data['css_variables']);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $vars
     * @return array<string, string>
     */
    private function normalizeSkinCssVariables(array $vars): array
    {
        $out = [];
        foreach ($vars as $k => $v) {
            if (! is_string($k) || $k === '') {
                continue;
            }
            if ($k === 'gradient-primary' || $k === 'gradient_primary') {
                continue;
            }
            if (is_string($v)) {
                $out[$k] = $v;
            }
        }
        $out['gradient-primary'] = $this->synthesizeGradientPrimary($out);

        return $out;
    }

    /**
     * @param  array<string, string>  $vars
     */
    private function synthesizeGradientPrimary(array $vars): string
    {
        $p = trim((string) ($vars['primary'] ?? ''));
        $s = trim((string) ($vars['secondary'] ?? ''));

        if ($p !== '' && $s !== '') {
            return "linear-gradient(135deg, {$p} 0%, {$s} 100%)";
        }

        if ($p !== '') {
            return "linear-gradient(135deg, {$p} 0%, {$p} 100%)";
        }

        return 'linear-gradient(135deg, #6366f1 0%, #ec4899 100%)';
    }

    /**
     * @param  array<string, mixed>  $vars
     */
    private function assertRequiredSkinCssVariablesPresent(array $vars): void
    {
        $missing = [];
        foreach (self::REQUIRED_SKIN_CSS_KEYS as $key) {
            $v = $vars[$key] ?? null;
            if (! is_string($v) || trim($v) === '') {
                $missing[] = $key;
            }
        }

        if ($missing === []) {
            return;
        }

        $required = implode('、', self::REQUIRED_SKIN_CSS_KEYS);
        $lack = implode('、', $missing);

        throw ValidationException::withMessages([
            'css_variables' => "主题变量须包含必填项且不可删除：{$required}。当前缺少或为空：{$lack}。",
        ]);
    }
}
