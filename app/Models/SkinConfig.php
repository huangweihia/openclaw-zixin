<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkinConfig extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'preview_image',
        'css_variables',
        'type',
        'sort',
        'is_active',
    ];

    protected $casts = [
        'css_variables' => 'array',
        'is_active' => 'boolean',
    ];
}
