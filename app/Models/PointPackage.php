<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointPackage extends Model
{
    protected $fillable = [
        'name',
        'points_amount',
        'price_yuan',
        'sort_order',
        'is_active',
        'badge',
    ];

    protected $casts = [
        'points_amount' => 'integer',
        'price_yuan' => 'decimal:2',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];
}
