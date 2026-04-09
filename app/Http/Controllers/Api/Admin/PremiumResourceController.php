<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PremiumResource;
use App\Support\AdminUniqueCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PremiumResourceController extends Controller
{
    public function index(): JsonResponse
    {
        $rows = PremiumResource::query()->orderByDesc('id')->get();

        return response()->json(['resources' => $rows]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['slug'] = AdminUniqueCode::slug($data['title'], PremiumResource::class, 'slug', null, 'resource');
        $row = PremiumResource::query()->create($data);

        return response()->json(['message' => '已创建', 'resource' => $row], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = PremiumResource::query()->findOrFail($id);
        $data = $this->validated($request, $row->id);
        unset($data['slug']);
        $row->fill($data)->save();

        return response()->json(['message' => '已更新', 'resource' => $row->fresh()]);
    }

    public function destroy(int $id): JsonResponse
    {
        PremiumResource::query()->whereKey($id)->delete();

        return response()->json(['message' => '已删除']);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'type' => ['required', Rule::in(['pdf', 'video', 'cloud_drive', 'ebook'])],
            'content' => ['nullable', 'string'],
            'download_link' => ['nullable', 'string', 'max:500'],
            'extract_code' => ['nullable', 'string', 'max:20'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'tags' => ['nullable', 'array'],
            'visibility' => ['required', Rule::in(['public', 'vip'])],
            'download_count' => ['sometimes', 'integer', 'min:0'],
            'view_count' => ['sometimes', 'integer', 'min:0'],
            'like_count' => ['sometimes', 'integer', 'min:0'],
            'favorite_count' => ['sometimes', 'integer', 'min:0'],
        ]);
    }
}
