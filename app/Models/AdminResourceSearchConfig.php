<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminResourceSearchConfig extends Model
{
    protected $fillable = [
        'resource_class',
        'search_column_names',
    ];

    protected $casts = [
        'search_column_names' => 'array',
    ];
}
