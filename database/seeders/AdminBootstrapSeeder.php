<?php

namespace Database\Seeders;

use App\Models\AdminPermission;
use App\Models\AdminRole;
use App\Models\AdminUser;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminBootstrapSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'dahu@openclaw.test'],
            [
                'name' => 'dahu',
                'password' => 'mqq123456',
                'role' => 'admin',
                'is_banned' => false,
                'subscription_ends_at' => null,
            ]
        );

        if (Schema::hasTable('admin_users')) {
            AdminUser::query()->updateOrCreate(
                ['user_id' => $admin->id],
                [
                    'display_name' => 'dahu',
                    'is_active' => true,
                    'is_super' => true,
                ]
            );
        }

        if (
            Schema::hasTable('admin_roles')
            && Schema::hasTable('admin_permissions')
            && Schema::hasTable('admin_role_permissions')
            && Schema::hasTable('admin_user_roles')
        ) {
            $superRole = AdminRole::query()->updateOrCreate(
                ['key' => 'super-admin'],
                [
                    'name' => '超级管理员',
                    'description' => '默认初始化超级管理员角色',
                ]
            );

            $allPermission = AdminPermission::query()->updateOrCreate(
                ['key' => '*'],
                [
                    'module' => 'all',
                    'action' => 'all',
                    'description' => '全量权限',
                ]
            );

            foreach ($this->menuReadPermissions() as $permKey) {
                [$prefix, $module, $action] = array_pad(explode(':', $permKey), 3, null);
                AdminPermission::query()->updateOrCreate(
                    ['key' => $permKey],
                    [
                        'module' => $module,
                        'action' => $action,
                        'description' => "菜单可见权限（{$permKey}）",
                    ]
                );
            }

            $rolesWrite = AdminPermission::query()->updateOrCreate(
                ['key' => 'admin:roles:write'],
                [
                    'module' => 'roles',
                    'action' => 'write',
                    'description' => '创建/编辑/删除后台角色与菜单配置',
                ]
            );

            $menusWrite = AdminPermission::query()->updateOrCreate(
                ['key' => 'admin:menus:write'],
                [
                    'module' => 'menus',
                    'action' => 'write',
                    'description' => '编辑后台侧边栏分组与菜单项',
                ]
            );

            $superRole->permissions()->syncWithoutDetaching([$allPermission->id, $rolesWrite->id, $menusWrite->id]);
            $admin->adminRoles()->syncWithoutDetaching([$superRole->id]);
        }

        if (Schema::hasTable('email_settings')) {
            $now = now();
            $rows = [
                ['name' => 'SMTP 主机', 'key' => 'mail.host', 'value' => 'smtp.qq.com', 'description' => '默认 SMTP 主机'],
                ['name' => 'SMTP 端口', 'key' => 'mail.port', 'value' => '465', 'description' => '默认 SMTP 端口'],
                ['name' => 'SMTP 加密', 'key' => 'mail.encryption', 'value' => 'ssl', 'description' => 'ssl / tls'],
                ['name' => '发件人地址', 'key' => 'mail.from_address', 'value' => 'dahu@openclaw.test', 'description' => '默认发件人邮箱'],
                ['name' => '发件人名称', 'key' => 'mail.from_name', 'value' => 'OpenClaw 智信', 'description' => '默认发件人名称'],
            ];
            foreach ($rows as $row) {
                DB::table('email_settings')->updateOrInsert(
                    ['key' => $row['key']],
                    [
                        'name' => $row['name'],
                        'value' => $row['value'],
                        'description' => $row['description'],
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }
    }

    /**
     * 后台菜单 read 权限基线（方案 A）。
     *
     * @return array<int, string>
     */
    private function menuReadPermissions(): array
    {
        return [
            'admin:dashboard:read',
            'admin:articles:read',
            'admin:projects:read',
            'admin:categories:read',
            'admin:premium-resources:read',
            'admin:side-hustle-cases:read',
            'admin:private-traffic-sops:read',
            'admin:ai-tool-monetization:read',
            'admin:moderation:read',
            'admin:comments:read',
            'admin:orders:read',
            'admin:subscriptions:read',
            'admin:svip-custom-subscriptions:read',
            'admin:view-histories:read',
            'admin:users:read',
            'admin:points-ledger:read',
            'admin:refund-requests:read',
            'admin:invoice-requests:read',
            'admin:comment-reports:read',
            'admin:email-templates:read',
            'admin:email-logs:read',
            'admin:email-subscriptions:read',
            'admin:site-testimonials:read',
            'admin:announcements:read',
            'admin:system-notifications:read',
            'admin:push-notifications:read',
            'admin:skin-configs:read',
            'admin:ad-slots:read',
            'admin:openclaw-task-logs:read',
            'admin:settings:read',
            'admin:email-settings:read',
            'admin:shared-components:read',
            'admin:audit-logs:read',
            'admin:publish-audits:read',
            'admin:roles:read',
            'admin:menus:read',
        ];
    }
}

