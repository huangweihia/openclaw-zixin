<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdSlot extends Model
{
    protected $fillable = [
        'name',
        'code',
        'position',
        'type',
        'width',
        'height',
        'is_active',
        'sort',
        'default_title',
        'default_image_url',
        'default_link_url',
        'default_content',
        'show_default_when_empty',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_default_when_empty' => 'boolean',
    ];

}
