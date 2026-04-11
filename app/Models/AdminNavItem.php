<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $admin_nav_section_id
 * @property string $menu_key
 * @property string $label
 * @property string|null $path
 * @property string|null $external_url
 * @property string|null $icon
 * @property string $perm_key
 * @property int $sort_order
 * @property bool $match_exact
 * @property bool $is_active
 */
class AdminNavItem extends Model
{
    protected $fillable = [
        'admin_nav_section_id',
        'menu_key',
        'label',
        'path',
        'external_url',
        'icon',
        'perm_key',
        'sort_order',
        'match_exact',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'match_exact' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(AdminNavSection::class, 'admin_nav_section_id');
    }
}
