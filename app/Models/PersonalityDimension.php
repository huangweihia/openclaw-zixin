<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonalityDimension extends Model
{
    protected $fillable = [
        'code',
        'name',
        'model_group',
        'sort_order',
        'explanation_l',
        'explanation_m',
        'explanation_h',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(PersonalityQuestion::class, 'personality_dimension_id')->orderBy('sort_order');
    }
}
