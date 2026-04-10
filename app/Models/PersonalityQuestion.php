<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonalityQuestion extends Model
{
    protected $fillable = [
        'personality_dimension_id',
        'body',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function dimension(): BelongsTo
    {
        return $this->belongsTo(PersonalityDimension::class, 'personality_dimension_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(PersonalityQuestionOption::class, 'personality_question_id')->orderBy('sort_order');
    }
}
