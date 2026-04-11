<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNavItem;
use App\Models\AdminNavSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminNavConfigController extends Controller
{
    public function index(): JsonResponse
    {
        $sections = AdminNavSection::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->with(['items' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')])
            ->get();

        return response()->json(['sections' => $sections]);
    }

    public function storeSection(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $section = AdminNavSection::query()->create([
            'title' => $data['title'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return response()->json(['section' => $section], 201);
    }

    public function updateSection(Request $request, AdminNavSection $adminNavSection): JsonResponse
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:100'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $adminNavSection->fill($data);
        $adminNavSection->save();

        return response()->json(['section' => $adminNavSection->fresh()]);
    }

    public function destroySection(AdminNavSection $adminNavSection): JsonResponse
    {
        $adminNavSection->delete();

        return response()->json(['ok' => true]);
    }

    public function storeItem(Request $request): JsonResponse
    {
        $data = $this->validatedItem($request, null);

        $item = AdminNavItem::query()->create($data);

        return response()->json(['item' => $item->fresh()], 201);
    }

    public function updateItem(Request $request, AdminNavItem $adminNavItem): JsonResponse
    {
        $data = $this->validatedItem($request, $adminNavItem);

        $adminNavItem->fill($data);
        $adminNavItem->save();

        return response()->json(['item' => $adminNavItem->fresh()]);
    }

    public function destroyItem(AdminNavItem $adminNavItem): JsonResponse
    {
        $adminNavItem->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedItem(Request $request, ?AdminNavItem $existing): array
    {
        $uniqueKey = Rule::unique('admin_nav_items', 'menu_key');
        if ($existing !== null) {
            $uniqueKey = $uniqueKey->ignore($existing->id);
        }

        $data = $request->validate([
            'admin_nav_section_id' => [
                $existing === null ? 'required' : 'sometimes',
                'integer',
                'exists:admin_nav_sections,id',
            ],
            'menu_key' => ['required', 'string', 'max:128', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $uniqueKey],
            'label' => ['required', 'string', 'max:200'],
            'path' => ['nullable', 'string', 'max:512'],
            'external_url' => ['nullable', 'string', 'max:1024'],
            'icon' => ['nullable', 'string', 'max:64'],
            'perm_key' => ['required', 'string', 'max:128'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:999999'],
            'match_exact' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $path = array_key_exists('path', $data) ? trim((string) $data['path']) : '';
        $ext = array_key_exists('external_url', $data) ? trim((string) $data['external_url']) : '';

        if ($ext === '' && $path === '') {
            throw ValidationException::withMessages([
                'path' => ['请填写站内路径，或填写外链地址。'],
            ]);
        }

        if ($ext !== '') {
            Validator::make(['external_url' => $ext], ['external_url' => ['required', 'url']])->validate();
        }

        if ($path !== '' && $ext === '' && $path[0] !== '/') {
            throw ValidationException::withMessages([
                'path' => ['站内路径须以 / 开头。'],
            ]);
        }

        $data['path'] = $path === '' ? null : $path;
        $data['external_url'] = $ext === '' ? null : $ext;
        $data['sort_order'] = (int) ($data['sort_order'] ?? $existing?->sort_order ?? 0);
        $data['match_exact'] = array_key_exists('match_exact', $data)
            ? (bool) $data['match_exact']
            : (bool) ($existing?->match_exact ?? false);
        $data['is_active'] = array_key_exists('is_active', $data)
            ? (bool) $data['is_active']
            : (bool) ($existing?->is_active ?? true);

        return $data;
    }
}
