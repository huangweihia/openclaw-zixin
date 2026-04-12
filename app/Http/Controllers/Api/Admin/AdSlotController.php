<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdSlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdSlotController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(10, min($perPage, 100));
        $q = trim((string) $request->query('q', ''));

        $slots = AdSlot::query()
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', '%'.$q.'%')
                        ->orWhere('code', 'like', '%'.$q.'%');
                });
            })
            ->orderByDesc('sort')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($slots);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'position' => ['required', Rule::in(['top', 'bottom', 'left', 'right'])],
            'type' => ['required', 'string', 'max:40'],
            'audience' => ['sometimes', Rule::in(['all', 'guest', 'user', 'vip', 'svip', 'admin', 'member', 'non_member'])],
            'width' => ['nullable', 'integer', 'min:0'],
            'height' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'sort' => ['sometimes', 'integer'],
            'default_title' => ['nullable', 'string', 'max:255'],
            'default_image_url' => ['nullable', 'string', 'max:500'],
            'default_link_url' => ['nullable', 'string', 'max:500'],
            'default_content' => ['nullable', 'string'],
            'show_default_when_empty' => ['sometimes', 'boolean'],
        ]);
        if (($data['is_active'] ?? false) === true) {
            AdSlot::query()->where('is_active', true)->update(['is_active' => false]);
        }
        $data['audience'] = $data['audience'] ?? 'all';
        $slot = AdSlot::query()->create($data);

        return response()->json([
            'message' => '广告位已创建',
            'slot' => $slot,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $slot = AdSlot::query()->findOrFail($id);
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'position' => ['sometimes', Rule::in(['top', 'bottom', 'left', 'right'])],
            'type' => ['sometimes', 'string', 'max:40'],
            'audience' => ['sometimes', Rule::in(['all', 'guest', 'user', 'vip', 'svip', 'admin', 'member', 'non_member'])],
            'width' => ['nullable', 'integer', 'min:0'],
            'height' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'sort' => ['sometimes', 'integer'],
            'default_title' => ['nullable', 'string', 'max:255'],
            'default_image_url' => ['nullable', 'string', 'max:500'],
            'default_link_url' => ['nullable', 'string', 'max:500'],
            'default_content' => ['nullable', 'string'],
            'show_default_when_empty' => ['sometimes', 'boolean'],
        ]);
        unset($data['code']);
        if (($data['is_active'] ?? false) === true) {
            AdSlot::query()->where('id', '!=', $slot->id)->where('is_active', true)->update(['is_active' => false]);
        }
        $slot->fill($data)->save();

        return response()->json(['message' => '已更新', 'slot' => $slot->fresh()]);
    }

    public function toggle(int $id): JsonResponse
    {
        $slot = AdSlot::query()->findOrFail($id);
        $next = ! $slot->is_active;
        if ($next) {
            AdSlot::query()->where('id', '!=', $slot->id)->where('is_active', true)->update(['is_active' => false]);
        }
        $slot->forceFill(['is_active' => $next])->save();

        return response()->json([
            'message' => $slot->is_active ? '已启用' : '已禁用',
            'slot' => $slot->fresh(),
        ]);
    }
}
