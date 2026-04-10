<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalityQuestionOption extends Model
{
    protected $fillable = [
        'personality_question_id',
        'label',
        'value',
        'sort_order',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(PersonalityQuestion::class, 'personality_question_id');
    }
}
