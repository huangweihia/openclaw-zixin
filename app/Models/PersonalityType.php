<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalityType extends Model
{
    protected $fillable = [
        'code',
        'cn_name',
        'intro',
        'description',
        'pattern',
        'is_fallback',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_fallback' => 'boolean',
        'is_active' => 'boolean',
    ];
}
