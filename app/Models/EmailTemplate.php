<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'name',
        'key',
        'subject',
        'content',
        'plain_text',
        'variables',
        'builder_layout',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'builder_layout' => 'array',
        'is_active' => 'boolean',
    ];
}
