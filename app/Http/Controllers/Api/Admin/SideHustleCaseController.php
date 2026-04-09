<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SideHustleCase;
use App\Support\AdminUniqueCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SideHustleCaseController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'cases' => SideHustleCase::query()
                ->with('user:id,name')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['slug'] = AdminUniqueCode::slug($data['title'], SideHustleCase::class, 'slug', null, 'case');
        $row = SideHustleCase::query()->create($data);

        return response()->json(['message' => '已创建', 'case' => $row->load('user:id,name')], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = SideHustleCase::query()->findOrFail($id);
        $data = $this->validated($request, $row->id);
        unset($data['slug']);
        $row->fill($data)->save();

        return response()->json(['message' => '已更新', 'case' => $row->fresh()->load('user:id,name')]);
    }

    public function destroy(int $id): JsonResponse
    {
        SideHustleCase::query()->whereKey($id)->delete();

        return response()->json(['message' => '已删除']);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'category' => ['required', Rule::in(['online', 'offline', 'hybrid'])],
            'type' => ['required', Rule::in(['ecommerce', 'content', 'service', 'other'])],
            'startup_cost' => ['sometimes', 'string', 'max:50'],
            'time_investment' => ['required', 'string', 'max:100'],
            'estimated_income' => ['sometimes', 'numeric', 'min:0'],
            'actual_income' => ['nullable', 'numeric', 'min:0'],
            'income_screenshots' => ['nullable', 'array'],
            'steps' => ['nullable', 'string'],
            'tools' => ['nullable', 'array'],
            'pitfalls' => ['nullable', 'array'],
            'willing_to_consult' => ['sometimes', 'boolean'],
            'contact_info' => ['nullable', 'string', 'max:255'],
            'visibility' => ['required', Rule::in(['public', 'vip', 'private'])],
            'status' => ['sometimes', Rule::in(['pending', 'approved', 'rejected'])],
            'audit_note' => ['nullable', 'string'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'view_count' => ['sometimes', 'integer', 'min:0'],
            'like_count' => ['sometimes', 'integer', 'min:0'],
            'comment_count' => ['sometimes', 'integer', 'min:0'],
            'favorite_count' => ['sometimes', 'integer', 'min:0'],
        ]);
    }
}
