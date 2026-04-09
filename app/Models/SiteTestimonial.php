<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteTestimonial extends Model
{
    protected $fillable = [
        'display_name',
        'caption',
        'body',
        'rating',
        'avatar_initial',
        'gradient_from',
        'gradient_to',
        'sort_order',
        'is_published',
    ];

    protected $casts = [
        'rating' => 'integer',
        'sort_order' => 'integer',
        'is_published' => 'boolean',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('sort_order')->orderByDesc('id');
    }
}
