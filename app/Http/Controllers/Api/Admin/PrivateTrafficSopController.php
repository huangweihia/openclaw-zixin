<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrivateTrafficSop;
use App\Support\AdminUniqueCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PrivateTrafficSopController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(10, min($perPage, 100));
        $q = trim((string) $request->query('q', ''));

        $rows = PrivateTrafficSop::query()
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($sub) use ($q) {
                    $sub->where('title', 'like', '%'.$q.'%')
                        ->orWhere('slug', 'like', '%'.$q.'%')
                        ->orWhere('summary', 'like', '%'.$q.'%');
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($rows);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['slug'] = AdminUniqueCode::slug($data['title'], PrivateTrafficSop::class, 'slug', null, 'sop');
        $row = PrivateTrafficSop::query()->create($data);

        return response()->json(['message' => '已创建', 'sop' => $row], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = PrivateTrafficSop::query()->findOrFail($id);
        $data = $this->validated($request, $row->id);
        unset($data['slug']);
        $row->fill($data)->save();

        return response()->json(['message' => '已更新', 'sop' => $row->fresh()]);
    }

    public function destroy(int $id): JsonResponse
    {
        PrivateTrafficSop::query()->whereKey($id)->delete();

        return response()->json(['message' => '已删除']);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'platform' => ['required', Rule::in(['wechat', 'xiaohongshu', 'douyin', 'other'])],
            'type' => ['required', Rule::in(['traffic', 'operation', 'conversion', 'retention'])],
            'checklist' => ['nullable', 'array'],
            'templates' => ['nullable', 'array'],
            'metrics' => ['nullable', 'array'],
            'tools' => ['nullable', 'array'],
            'contact_note' => ['nullable', 'string', 'max:5000'],
            'vip_gate_engagement' => ['sometimes', 'boolean'],
            'visibility' => ['required', Rule::in(['public', 'vip'])],
            'view_count' => ['sometimes', 'integer', 'min:0'],
            'like_count' => ['sometimes', 'integer', 'min:0'],
            'favorite_count' => ['sometimes', 'integer', 'min:0'],
        ]);
    }
}
