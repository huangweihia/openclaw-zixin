<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('admin_nav_sections') || ! Schema::hasTable('admin_nav_items')) {
            return;
        }

        $sectionId = DB::table('admin_nav_sections')
            ->where('title', '交易 · 用户 · 售后')
            ->value('id');
        if (! $sectionId) {
            return;
        }

        $now = now();
        $items = [
            ['menu_key' => 'point-packages', 'label' => '积分套餐', 'path' => '/point-packages', 'icon' => 'ep:Coin', 'perm_key' => 'admin:point-packages:read', 'sort_order' => 55],
            ['menu_key' => 'content-boosts', 'label' => '加热记录', 'path' => '/content-boosts', 'icon' => 'ep:Fire', 'perm_key' => 'admin:content-boosts:read', 'sort_order' => 56],
        ];

        foreach ($items as $row) {
            $exists = DB::table('admin_nav_items')->where('menu_key', $row['menu_key'])->exists();
            if (! $exists) {
                DB::table('admin_nav_items')->insert([
                    'admin_nav_section_id' => $sectionId,
                    'menu_key' => $row['menu_key'],
                    'label' => $row['label'],
                    'path' => $row['path'],
                    'external_url' => null,
                    'icon' => $row['icon'],
                    'perm_key' => $row['perm_key'],
                    'sort_order' => $row['sort_order'],
                    'match_exact' => false,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        if (Schema::hasTable('admin_permissions')) {
            $perms = [
                ['module' => 'point-packages', 'action' => 'read', 'key' => 'admin:point-packages:read', 'description' => '查看积分套餐'],
                ['module' => 'content-boosts', 'action' => 'read', 'key' => 'admin:content-boosts:read', 'description' => '查看加热记录'],
            ];
            foreach ($perms as $perm) {
                DB::table('admin_permissions')->updateOrInsert(
                    ['key' => $perm['key']],
                    array_merge($perm, ['updated_at' => $now, 'created_at' => $now]),
                );
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('admin_nav_items')) {
            DB::table('admin_nav_items')->whereIn('menu_key', ['point-packages', 'content-boosts'])->delete();
        }
        if (Schema::hasTable('admin_permissions')) {
            DB::table('admin_permissions')->whereIn('key', ['admin:point-packages:read', 'admin:content-boosts:read'])->delete();
        }
    }
};
