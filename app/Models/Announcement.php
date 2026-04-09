<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'priority',
        'display_position',
        'is_floating',
        'cover_image',
        'float_width',
        'float_height',
        'is_published',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'float_width' => 'integer',
        'float_height' => 'integer',
        'is_published' => 'boolean',
        'is_floating' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
