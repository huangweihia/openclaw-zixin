<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiToolMonetization;
use App\Support\AdminUniqueCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AiToolMonetizationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'tools' => AiToolMonetization::query()->orderByDesc('id')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['slug'] = AdminUniqueCode::slug($data['tool_name'], AiToolMonetization::class, 'slug', null, 'ai');
        $row = AiToolMonetization::query()->create($data);

        return response()->json(['message' => '已创建', 'tool' => $row], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = AiToolMonetization::query()->findOrFail($id);
        $data = $this->validated($request, $row->id);
        unset($data['slug']);
        $row->fill($data)->save();

        return response()->json(['message' => '已更新', 'tool' => $row->fresh()]);
    }

    public function destroy(int $id): JsonResponse
    {
        AiToolMonetization::query()->whereKey($id)->delete();

        return response()->json(['message' => '已删除']);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'tool_name' => ['required', 'string', 'max:255'],
            'tool_url' => ['nullable', 'string', 'max:255'],
            'category' => ['required', Rule::in(['image', 'text', 'video', 'audio', 'code'])],
            'available_in_china' => ['sometimes', 'boolean'],
            'pricing_model' => ['required', Rule::in(['free', 'subscription', 'pay_as_you_go'])],
            'content' => ['nullable', 'string'],
            'monetization_scenes' => ['nullable', 'array'],
            'prompt_templates' => ['nullable', 'array'],
            'pricing_reference' => ['nullable', 'array'],
            'channels' => ['nullable', 'array'],
            'delivery_standards' => ['nullable', 'array'],
            'visibility' => ['required', Rule::in(['public', 'vip'])],
            'view_count' => ['sometimes', 'integer', 'min:0'],
            'like_count' => ['sometimes', 'integer', 'min:0'],
            'favorite_count' => ['sometimes', 'integer', 'min:0'],
        ]);
    }
}
