<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Support\AdminNavRegistry;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'bio',
        'enterprise_wechat_id',
        'privacy_mode',
        'role',
        'is_banned',
        'subscription_ends_at',
        'points_balance',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_banned' => 'boolean',
        'privacy_mode' => 'boolean',
        'subscription_ends_at' => 'datetime',
        'points_balance' => 'integer',
        'last_login_at' => 'datetime',
    ];

    public function inboxNotifications(): HasMany
    {
        return $this->hasMany(InboxNotification::class, 'user_id');
    }

    public function emailSubscriptions(): HasMany
    {
        return $this->hasMany(EmailSubscription::class, 'user_id');
    }

    public function pointLedgers(): HasMany
    {
        return $this->hasMany(\App\Models\Point::class, 'user_id')->orderByDesc('id');
    }
    
    public function isVip(): bool
    {
        return in_array($this->role, ['vip', 'svip'], true);
    }
    
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * 顶栏会员区 / VIP 皮肤等：管理员与付费会员同等对待，不提示「开通 VIP」。
     */
    public function hasMemberMenuPrivileges(): bool
    {
        return in_array($this->role, ['vip', 'svip', 'admin'], true);
    }

    public function adminRoles(): BelongsToMany
    {
        return $this->belongsToMany(AdminRole::class, 'admin_user_roles');
    }

    public function adminUser(): HasOne
    {
        return $this->hasOne(AdminUser::class, 'user_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() !== 'admin') {
            return false;
        }

        if ($this->role !== 'admin' || $this->is_banned) {
            return false;
        }

        $profile = $this->relationLoaded('adminUser') ? $this->adminUser : $this->adminUser()->first();

        if (! $profile) {
            AdminUser::query()->create([
                'user_id' => $this->id,
                'display_name' => $this->name ?: null,
                'is_active' => true,
                'is_super' => false,
            ]);
            $this->unsetRelation('adminUser');
            $profile = $this->adminUser()->first();
        }

        return $profile !== null && $profile->is_active;
    }

    public function adminPermissions(): array
    {
        $this->loadMissing('adminUser');
        if ($this->adminUser?->is_super) {
            return ['*'];
        }

        if (! Schema::hasTable('admin_user_roles')) {
            return $this->role === 'admin' ? ['*'] : [];
        }

        $roles = $this->adminRoles()->with('permissions')->get();

        $keys = [];
        foreach ($roles as $role) {
            foreach ($role->permissions as $permission) {
                if (! empty($permission->key)) {
                    $keys[] = $permission->key;
                }
            }
        }

        $keys = array_values(array_unique($keys));

        // 兼容：在尚未配置 RBAC 前，admin 默认拥有全权限（避免上线后菜单/按钮全部消失）
        if ($this->role === 'admin' && empty($keys)) {
            $keys = ['*'];
        }

        sort($keys);

        return $keys;
    }

    /**
     * 是否可在侧边栏 / 路由层访问某 admin_nav_items.menu_key（权限 + 角色菜单白名单）。
     */
    public function allowsAdminMenuKey(string $menuKey): bool
    {
        if ($this->role !== 'admin' || $this->is_banned) {
            return false;
        }

        $this->loadMissing('adminUser');
        if ($this->adminUser?->is_super) {
            return true;
        }

        $item = AdminNavRegistry::item($menuKey);
        if ($item === null || ! $item->is_active) {
            return false;
        }

        $perms = $this->adminPermissions();
        $hasStar = in_array('*', $perms, true);
        if (! $hasStar && ! in_array($item->perm_key, $perms, true)) {
            return false;
        }

        if (! Schema::hasTable('admin_user_roles') || ! Schema::hasTable('admin_roles')) {
            return true;
        }

        $roles = $this->adminRoles()->with('menuItems')->get();
        if ($roles->isEmpty()) {
            return true;
        }

        $anyInherit = $roles->contains(fn ($r) => ($r->menu_mode ?? 'inherit') !== 'whitelist');
        if ($anyInherit) {
            return true;
        }

        foreach ($roles as $role) {
            if ($role->menuItems->contains(fn ($m) => $m->menu_key === $menuKey)) {
                return true;
            }
        }

        return false;
    }
}
