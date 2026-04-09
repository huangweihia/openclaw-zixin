<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkinConfig extends Model
{
    protected $fillable = [
        'name',
        'code',
        'owner_user_id',
        'description',
        'preview_image',
        'css_variables',
        'type',
        'is_private',
        'custom_source',
        'sort',
        'is_active',
    ];

    protected $casts = [
        'css_variables' => 'array',
        'is_private' => 'boolean',
        'is_active' => 'boolean',
    ];
}
