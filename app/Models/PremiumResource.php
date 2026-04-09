<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PremiumResource extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'summary',
        'type',
        'content',
        'download_link',
        'extract_code',
        'original_price',
        'tags',
        'visibility',
        'download_count',
        'view_count',
        'like_count',
        'favorite_count',
    ];

    protected $casts = [
        'tags' => 'array',
        'original_price' => 'decimal:2',
    ];
}
