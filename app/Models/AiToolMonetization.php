<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiToolMonetization extends Model
{
    protected $table = 'ai_tool_monetization';

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $fillable = [
        'tool_name',
        'slug',
        'tool_url',
        'category',
        'available_in_china',
        'pricing_model',
        'content',
        'monetization_scenes',
        'prompt_templates',
        'pricing_reference',
        'channels',
        'delivery_standards',
        'visibility',
        'view_count',
        'like_count',
        'favorite_count',
    ];

    protected $casts = [
        'available_in_china' => 'boolean',
        'monetization_scenes' => 'array',
        'prompt_templates' => 'array',
        'pricing_reference' => 'array',
        'channels' => 'array',
        'delivery_standards' => 'array',
    ];
}
