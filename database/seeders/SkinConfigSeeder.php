<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;

class SkinConfigSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('skin_configs')) {
            return;
        }

        $now = now();
        foreach ($this->themes() as $row) {
            DB::table('skin_configs')->updateOrInsert(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'type' => $row['type'],
                    'sort' => $row['sort'],
                    'is_active' => $row['is_active'],
                    'css_variables' => json_encode($row['css_variables'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    /**
     * 与 database/skins_data.sql 对齐（style1~style7）。
     *
     * @return array<int, array<string,mixed>>
     */
    private function themes(): array
    {
        return [
            [
                'name' => '深空蓝',
                'code' => 'style1',
                'description' => '默认皮肤，紫粉渐变，对应 App.vue style1 / :root 默认',
                'type' => 'free',
                'sort' => 1,
                'is_active' => 1,
                'css_variables' => [
                    'primary' => '#6366f1',
                    'primary-dark' => '#4f46e5',
                    'primary-light' => '#818cf8',
                    'secondary' => '#ec4899',
                    'gradient-primary' => 'linear-gradient(135deg, #6366f1 0%, #ec4899 100%)',
                    'bg-primary' => '#f1f5f9',
                    'bg-secondary' => '#ffffff',
                    'text-primary' => '#0f172a',
                    'text-secondary' => '#64748b',
                    'border-color' => '#e2e8f0',
                ],
            ],
            [
                'name' => '商务蓝',
                'code' => 'style2',
                'description' => '深蓝渐变，专业稳重，对应 App.vue style2',
                'type' => 'free',
                'sort' => 2,
                'is_active' => 1,
                'css_variables' => [
                    'primary' => '#3b82f6',
                    'primary-dark' => '#2563eb',
                    'primary-light' => '#60a5fa',
                    'secondary' => '#1e40af',
                    'gradient-primary' => 'linear-gradient(135deg, #3b82f6 0%, #1e40af 100%)',
                    'bg-primary' => '#f3f4f6',
                    'bg-secondary' => '#ffffff',
                    'text-primary' => '#1e3a8a',
                    'text-secondary' => '#6b7280',
                    'border-color' => '#dbeafe',
                ],
            ],
            [
                'name' => '清新绿',
                'code' => 'style3',
                'description' => '绿色渐变，自然清新，对应 App.vue style3',
                'type' => 'free',
                'sort' => 3,
                'is_active' => 1,
                'css_variables' => [
                    'primary' => '#10b981',
                    'primary-dark' => '#059669',
                    'primary-light' => '#34d399',
                    'secondary' => '#14b8a6',
                    'gradient-primary' => 'linear-gradient(135deg, #10b981 0%, #14b8a6 100%)',
                    'bg-primary' => '#d1fae5',
                    'bg-secondary' => '#ffffff',
                    'text-primary' => '#064e3b',
                    'text-secondary' => '#6b7280',
                    'border-color' => '#a7f3d0',
                ],
            ],
            [
                'name' => '暖橙风',
                'code' => 'style4',
                'description' => '橙红渐变，温暖活力，对应 App.vue style4',
                'type' => 'free',
                'sort' => 4,
                'is_active' => 1,
                'css_variables' => [
                    'primary' => '#f59e0b',
                    'primary-dark' => '#d97706',
                    'primary-light' => '#fbbf24',
                    'secondary' => '#ea580c',
                    'gradient-primary' => 'linear-gradient(135deg, #f59e0b 0%, #ea580c 100%)',
                    'bg-primary' => '#fef3c7',
                    'bg-secondary' => '#ffffff',
                    'text-primary' => '#7c2d12',
                    'text-secondary' => '#6b7280',
                    'border-color' => '#fde68a',
                ],
            ],
            [
                'name' => '暗夜黑',
                'code' => 'style5',
                'description' => '深色界面，对应 App.vue style5；演示环境可作为 VIP 示例皮肤',
                'type' => 'vip',
                'sort' => 5,
                'is_active' => 1,
                'css_variables' => [
                    'primary' => '#000000',
                    'primary-dark' => '#1a1a1a',
                    'primary-light' => '#404040',
                    'secondary' => '#262626',
                    'gradient-primary' => 'linear-gradient(135deg, #000000 0%, #1a1a1a 100%)',
                    'bg-primary' => '#0f172a',
                    'bg-secondary' => '#1e293b',
                    'text-primary' => '#f1f5f9',
                    'text-secondary' => '#94a3b8',
                    'border-color' => '#334155',
                ],
            ],
            [
                'name' => '深空蓝·首页',
                'code' => 'style6',
                'description' => '与深空蓝同色，对应 App.vue 深空蓝首页 style6',
                'type' => 'free',
                'sort' => 6,
                'is_active' => 1,
                'css_variables' => [
                    'primary' => '#6366f1',
                    'primary-dark' => '#4f46e5',
                    'primary-light' => '#818cf8',
                    'secondary' => '#ec4899',
                    'gradient-primary' => 'linear-gradient(135deg, #6366f1 0%, #ec4899 100%)',
                    'bg-primary' => '#f1f5f9',
                    'bg-secondary' => '#ffffff',
                    'text-primary' => '#0f172a',
                    'text-secondary' => '#64748b',
                    'border-color' => '#e2e8f0',
                ],
            ],
            [
                'name' => '护眼绿·首页',
                'code' => 'style7',
                'description' => '柔和绿色背景，对应 App.vue 护眼绿首页 style7',
                'type' => 'free',
                'sort' => 7,
                'is_active' => 1,
                'css_variables' => [
                    'primary' => '#10b981',
                    'primary-dark' => '#059669',
                    'primary-light' => '#34d399',
                    'secondary' => '#14b8a6',
                    'gradient-primary' => 'linear-gradient(135deg, #10b981 0%, #14b8a6 100%)',
                    'bg-primary' => '#e8f5e9',
                    'bg-secondary' => '#ffffff',
                    'text-primary' => '#0c1a12',
                    'text-secondary' => '#5c7c70',
                    'border-color' => '#c8e6c9',
                ],
            ],
        ];
    }
}
