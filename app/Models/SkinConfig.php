<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }
}
