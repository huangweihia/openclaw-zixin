<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminPermission;
use App\Models\AdminRole;
use App\Models\AdminRoleMenuItem;
use App\Support\AdminMenuCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminRoleController extends Controller
{
    /**
     * 新建角色时的选项（菜单目录、权限列表）。
     */
    public function formOptions(): JsonResponse
    {
        return response()->json([
            'menu_catalog' => AdminMenuCatalog::forEditor(),
            'all_permissions' => AdminPermission::query()->orderBy('key')->get(['id', 'key', 'module', 'action', 'description']),
        ]);
    }

    public function index(): JsonResponse
    {
        $roles = AdminRole::query()
            ->withCount('menuItems')
            ->orderBy('id')
            ->get()
            ->map(fn (AdminRole $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'key' => $r->key,
                'description' => $r->description,
                'menu_mode' => $r->menu_mode ?? 'inherit',
                'menu_items_count' => (int) $r->menu_items_count,
            ]);

        return response()->json(['roles' => $roles]);
    }

    public function show(AdminRole $adminRole): JsonResponse
    {
        $adminRole->load(['permissions:id,key,description', 'menuItems']);

        return response()->json([
            'role' => $this->serializeRoleDetail($adminRole),
            'menu_catalog' => AdminMenuCatalog::forEditor(),
            'all_permissions' => AdminPermission::query()->orderBy('key')->get(['id', 'key', 'module', 'action', 'description']),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $role = AdminRole::query()->create([
            'name' => $data['name'],
            'key' => $data['key'],
            'description' => $data['description'] ?? null,
            'menu_mode' => $data['menu_mode'],
        ]);
        $role->permissions()->sync($data['permission_ids'] ?? []);
        $this->syncMenuItems($role, $data['menu_keys'] ?? []);

        return response()->json([
            'message' => '已创建角色',
            'role' => $this->serializeRoleDetail($role->fresh(['permissions:id,key', 'menuItems'])),
        ], 201);
    }

    public function update(Request $request, AdminRole $adminRole): JsonResponse
    {
        if ($adminRole->key === 'super-admin') {
            $data = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:2000'],
            ]);
            if (isset($data['name'])) {
                $adminRole->name = $data['name'];
            }
            if (array_key_exists('description', $data)) {
                $adminRole->description = $data['description'];
            }
            $adminRole->save();

            return response()->json([
                'message' => '已更新角色',
                'role' => $this->serializeRoleDetail($adminRole->fresh(['permissions:id,key', 'menuItems'])),
            ]);
        }

        $data = $this->validated($request, $adminRole->id);
        $adminRole->forceFill([
            'name' => $data['name'],
            'key' => $data['key'],
            'description' => $data['description'] ?? null,
            'menu_mode' => $data['menu_mode'],
        ])->save();
        $adminRole->permissions()->sync($data['permission_ids'] ?? []);
        $this->syncMenuItems($adminRole, $data['menu_keys'] ?? []);

        return response()->json([
            'message' => '已更新角色',
            'role' => $this->serializeRoleDetail($adminRole->fresh(['permissions:id,key', 'menuItems'])),
        ]);
    }

    public function destroy(AdminRole $adminRole): JsonResponse
    {
        if ($adminRole->key === 'super-admin') {
            return response()->json(['message' => '不能删除内置超级管理员角色。'], 422);
        }

        $adminRole->delete();

        return response()->json(['message' => '已删除角色']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $ignoreRoleId = null): array
    {
        $uniqueKey = Rule::unique('admin_roles', 'key');
        if ($ignoreRoleId !== null) {
            $uniqueKey = $uniqueKey->ignore($ignoreRoleId);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $uniqueKey],
            'description' => ['nullable', 'string', 'max:2000'],
            'menu_mode' => ['required', Rule::in(['inherit', 'whitelist'])],
            'permission_ids' => ['sometimes', 'array'],
            'permission_ids.*' => ['integer', 'exists:admin_permissions,id'],
            'menu_keys' => ['sometimes', 'array'],
            'menu_keys.*' => ['string', Rule::in(AdminMenuCatalog::validKeys())],
        ]);
    }

    /**
     * @param  array<int, string>  $orderedKeys
     */
    private function syncMenuItems(AdminRole $role, array $orderedKeys): void
    {
        AdminRoleMenuItem::query()->where('admin_role_id', $role->id)->delete();
        if (($role->menu_mode ?? 'inherit') !== 'whitelist' || $orderedKeys === []) {
            return;
        }
        $rows = [];
        $order = 0;
        foreach ($orderedKeys as $key) {
            $key = (string) $key;
            if ($key === '' || ! in_array($key, AdminMenuCatalog::validKeys(), true)) {
                continue;
            }
            $rows[] = [
                'admin_role_id' => $role->id,
                'menu_key' => $key,
                'sort_order' => $order++,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if ($rows !== []) {
            AdminRoleMenuItem::query()->insert($rows);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeRoleDetail(AdminRole $role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'key' => $role->key,
            'description' => $role->description,
            'menu_mode' => $role->menu_mode ?? 'inherit',
            'permission_ids' => $role->permissions->pluck('id')->map(fn ($i) => (int) $i)->values()->all(),
            'menu_keys' => $role->menuItems->pluck('menu_key')->values()->all(),
        ];
    }
}
