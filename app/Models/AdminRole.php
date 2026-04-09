<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property string $key
 * @property string|null $description
 *
 * @property \Illuminate\Support\Collection<int, \App\Models\AdminPermission> $permissions
 */
class AdminRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key',
        'description',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(AdminPermission::class, 'admin_role_permissions');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'admin_user_roles');
    }
}

