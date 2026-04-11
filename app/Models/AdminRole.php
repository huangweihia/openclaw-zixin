<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $key
 * @property string|null $description
 * @property string $menu_mode inherit|whitelist
 *
 * @property \Illuminate\Support\Collection<int, \App\Models\AdminPermission> $permissions
 * @property \Illuminate\Support\Collection<int, \App\Models\AdminRoleMenuItem> $menuItems
 */
class AdminRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key',
        'description',
        'menu_mode',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(AdminPermission::class, 'admin_role_permissions');
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(AdminRoleMenuItem::class, 'admin_role_id')->orderBy('sort_order')->orderBy('id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'admin_user_roles');
    }
}

