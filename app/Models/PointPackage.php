<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointPackage extends Model
{
    protected $fillable = [
        'name',
        'points_amount',
        'bonus_points',
        'price_yuan',
        'sort_order',
        'is_active',
        'active_from',
        'active_until',
        'badge',
    ];

    protected $casts = [
        'points_amount' => 'integer',
        'bonus_points' => 'integer',
        'price_yuan' => 'decimal:2',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'active_from' => 'datetime',
        'active_until' => 'datetime',
    ];

    public function bonusPointsNow(): int
    {
        return max(0, (int) ($this->bonus_points ?? 0));
    }

    public function totalPointsNow(): int
    {
        return max(0, (int) ($this->points_amount ?? 0)) + $this->bonusPointsNow();
    }

    public function isAvailableNow(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();
        if ($this->active_from && $this->active_from->gt($now)) {
            return false;
        }
        if ($this->active_until && $this->active_until->lt($now)) {
            return false;
        }

        return true;
    }
}
