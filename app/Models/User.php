<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
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

    public function adminPermissions(): array
    {
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
}
